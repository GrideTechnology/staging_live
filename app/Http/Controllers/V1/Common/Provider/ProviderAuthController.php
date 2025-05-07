<?php

namespace App\Http\Controllers\V1\Common\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Common\ProviderDocument;
use App\Models\Common\ProviderAgreeDocument;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notification as NewPushNotification;
use App\Services\ReferralResource;
use App\Models\Common\CompanyCity;
use App\Models\Common\CompanyCountry;
use App\Services\SendPushNotification; 
use App\Notifications\WebPushNotification;
use App\Models\Common\Country;
use App\Models\Common\Document;
use App\Models\Common\Provider;
use App\Models\Common\Setting;
use App\Models\Common\AuthLog;
use App\Models\Common\Admin;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use App\Traits\Encryptable;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;
use Carbon\Carbon;
use Notification;
use Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class ProviderAuthController extends Controller
{
	protected $jwt;
	use Encryptable;

	public function __construct(JWTAuth $jwt)
	{
		$this->jwt = $jwt;
	}
	
	public function login(Request $request) {        				
		if($request->has('email')) {
            $request->merge([
                'email' => strtolower($request->email)
            ]);
		}        
		$this->validate($request, [
			'email'    => 'email|max:255',
			'password' => 'required',
			'salt_key' => 'required',
		]);
        
		if($request->has('email') && $request->get('email') != '') {
			$request->merge([
				'email' => $this->cusencrypt($request->get('email'),env('DB_SECRET'))            
			]);
		}

		if($request->has('mobile')) {
			$request->merge([
				'mobile' => $this->cusencrypt($request->get('mobile'),env('DB_SECRET'))            
			]);			
		}        		
		if(!$request->has('email') && !$request->has('mobile')) {			
			$this->validate($request, [
				'email'    => 'required|email|max:255',
				'mobile' => 'required',
				'country_code' => 'required'
			]);
		} else if(!$request->has('mobile')) {			
			$this->validate($request, [
				'email'    => ['required', 'max:255', Rule::exists('providers')]
			]);
		} else if(!$request->has('email')) {			
			$this->validate($request, [
				'mobile' => ['required', Rule::exists('providers')],
				'country_code' => 'required'
			], ['mobile.exists'=>'Invalid Credentials','email.exists'=>'Invalid Credentials']);
		}else{
			// return 5;
		}

		try {

			$request->request->add(['company_id' => base64_decode($request->get('salt_key'))]);
			$request->request->remove('salt_key');			
			if($request->has('email') && $request->get('email') != '') {                                
                if (! $token = Auth::guard('provider')->attempt($request->only('email', 'password', 'company_id')) ) {
                    return Helper::getResponse(['status' => 422, 'message' => 'Invalid Credentials']);
                }
            } else {	
				// Below old code is not working in some cases			
                // if (! $token = Auth::guard('provider')->attempt($request->only('country_code', 'mobile', 'password', 'company_id')) ) {					
                    // return Helper::getResponse(['status' => 422, 'message' => 'Invalid Credentials']);
                // }				
				$user = Provider::where(["mobile" => $request->get('mobile'),"company_id" => $request->get('company_id'),"country_code" => (int)$request->get('country_code')])->first();				
				if (!empty($user)) {
					if (!Hash::check($request->get('password'), $user->password)) {
						return Helper::getResponse(['status' => 422, 'message' => 'Invalid Credentials']);
					} else {						
						$token = Auth::guard('provider')->login($user);						
					}
				} else {
					return ['status' => 422, 'message' => 'User not found'];
				}				
            }

		} catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

			return Helper::getResponse(['status' => 500, 'message' => 'Token Expired']);

		} catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

			return Helper::getResponse(['status' => 500, 'message' => 'Token Invalid']);

		} catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

			return Helper::getResponse(['status' => 500, 'message' => $e->getMessage()]);

		}

		$User = Provider::find(Auth::guard('provider')->user()->id);
		if($User->activation_status == 0){
            return Helper::getResponse(['status' => 422, 'message' => 'Account Disabled']);
        }
		$User->device_type = $request->get('device_type');
		$User->device_token = $request->get('device_token');
        $User->login_by = ($request->get('login_by') != null) ? $request->get('login_by') : 'MANUAL' ;
        $User->is_online = 1;
		$User->save();

		AuthLog::create(['user_type' => 'Provider', 'user_id' => \Auth::guard('provider')->id(), 'type' => 'login', 'data' => json_encode(
			['data' => [ $request->getMethod() =>  $request->getPathInfo(). " " . $request->getProtocolVersion(), 
			'host' => $request->getHost(), 
			'ip' => $request->getClientIp(), 
			'user_agent' => $request->userAgent(), 
			'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]]
		)]);

		$newUser = Provider::find($User->id);
		$newUser->jwt_token = $token;
		$newUser->save();

		return Helper::getResponse(['data' => ["token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => $token, 'user' => $newUser ]]);

	}

	public function signup(Request $request) {        
		if($request->has('email')) {
            $request->merge([
                'email' => strtolower($request->email)
            ]);
        }

		$this->validate($request, [
			'social_unique_id' => ['required_if:login_by,GOOGLE,FACEBOOK','unique:providers'],
			'device_type' => 'in:ANDROID,IOS',
			'first_name' => 'required|max:255',
			'last_name' => 'required|max:255',
			'service_type' => 'required',
			'mobile' => 'required',
			'country_code' => 'required',
			'email' => 'required|email|max:255',
			'password' => ['required_if:login_by,MANUAL','min:6'],
			'salt_key' => 'required',
		]);

		$request->merge([
			'email' => $this->cusencrypt($request->email,env('DB_SECRET')),
			'mobile' => $this->cusencrypt($request->mobile,env('DB_SECRET')),
		]);

		$company_id=base64_decode($request->salt_key);
		$email=$request->email;
		$mobile=$request->mobile;
		$country_code=$request->country_code;
         
		$this->validate($request, [          
			'email' =>[ Rule::unique('providers')->where(function ($query) use($email,$company_id) {
							return $query->where('email', $email)->where('company_id', $company_id);
						 }),
					   ],
			'mobile' =>[ Rule::unique('providers')->where(function ($query) use($mobile,$company_id,$country_code) {
							return $query->where('mobile', $mobile)->where('country_code', $country_code)->where('company_id', $company_id);
						 }),
					   ],
		],['email.unique'=>'User already registered with given email-Id!','mobile.unique'=>'User already registered with given mobile number!']);
		

		$settings = json_decode(json_encode(Setting::where('company_id', base64_decode($request->salt_key))->first()->settings_data));

		$siteConfig = $settings->site;

		$transportConfig = $settings->transport;               

		if($request->has('referral_code') && $request->referral_code != ""){
			$validate['referral_unique_id']=$request->referral_code; 
			$validate['company_id']=$company_id;         
			$validator  = (new ReferralResource)->checkReferralCode($validate);        
			if (!$validator->fails()) { 
				$validator->errors()->add('referral_code', 'Invalid Referral Code');
				throw new \Illuminate\Validation\ValidationException($validator);
			}   
		}

		$referral_unique_id=(new ReferralResource)->generateCode($company_id);

		$request->merge([
			'email' => $this->cusdecrypt($request->email,env('DB_SECRET')),
			'mobile' => $this->cusdecrypt($request->mobile,env('DB_SECRET')),
		]);

		$city = CompanyCity::where('city_id', $request->city_id)->first();
				
		// if($city == null) {
		// 	$validator = Validator::make([], [], []);
        //     $validator->errors()->add('city', 'City does not exist!');
        //     throw new \Illuminate\Validation\ValidationException($validator);
        // }

        $country = CompanyCountry::where('company_id',$company_id)->where('country_id', $request->country_id)->first();

		$User = new Provider();
		$User->first_name = $request->first_name;
		$User->last_name = $request->last_name;
		$User->email = $request->email;
		$User->gender = $request->gender;
		$User->country_code = $request->country_code;
		$User->mobile = $request->mobile;
		$User->password = ($request->social_unique_id != null)  ? Hash::make($request->social_unique_id) : Hash::make($request->password) ;
		$User->referral_unique_id = $referral_unique_id;
		$User->company_id = base64_decode($request->salt_key);
		$User->social_unique_id = $request->social_unique_id;
		$User->device_type = $request->device_type;
		$User->device_token = $request->device_token;
		$User->social_unique_id = ($request->social_unique_id != null)  ? $request->social_unique_id : null ;
		$User->login_by = ($request->login_by != null) ? $request->login_by : 'MANUAL' ;
		$User->country_id = $request->country_id;
		$User->service_type = $request->service_type;
		if(!empty($city->state_id)){
			$User->state_id = $city->state_id;
		}
		if(!empty($request->city_id)){
			$User->city_id = $request->city_id;
		}

		if($request->has('subscription')){
			$User->subscription_id = $request->subscription;
		}
		
		
        $User->currency_symbol = $country->currency;
        $User->currency = $country->currency;
		$User->save();
		$createduser = Provider::findOrFail($User->id);
		if($request->hasFile('picture')) {
            $createduser->picture = Helper::upload_file($request->file('picture'), 'provider/profile', $User->id.'.'.$request->file('picture')->getClientOriginalExtension(), base64_decode($request->salt_key)); 
        }
        $createduser->unique_id=$siteConfig->provider_unique_id.$User->id;
		$createduser->qrcode_url = Helper::qrCode(json_encode(["country_code" => $request->country_code, 'phone_number' => $request->mobile]), $User->id.'.png', base64_decode($request->salt_key));
		$createduser->save();

		AuthLog::create(['user_type' => 'Provider', 'user_id' => \Auth::guard('provider')->id(), 'type' => 'login', 'data' => json_encode(
			['data' => [ $request->getMethod() =>  $request->getPathInfo(). " " . $request->getProtocolVersion(), 
			'host' => $request->getHost(), 
			'ip' => $request->getClientIp(), 
			'user_agent' => $request->userAgent(), 
			'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]]
		)]);

		$request->request->add(['company_id' => base64_decode($request->salt_key)]);  
			$request->request->remove('salt_key');

		$request->merge([
			'email' => $this->cusencrypt($request->email,env('DB_SECRET'))            
		]);   

		$credentials = ['email' => $request->email, 'password' => ($request->social_unique_id != null)  ? $request->social_unique_id : $request->password, 'company_id' => $User->company_id];
			
		$token = Auth::guard('provider')->attempt($credentials);

		if( !empty($siteConfig->send_email) && $siteConfig->send_email == 1) {
			// send welcome email here 
			Helper::siteRegisterMail($User);
		}    

		//check user referrals
		if( !empty($siteConfig->referral) && $siteConfig->referral == 1) {
			if($request->referral_code){
				//call referral function
				(new ReferralResource)->create_referral($request->referral_code, $User, $settings, 'provider');                
			}
		}

		$newUser = Provider::find($User->id); 
		$newUser->jwt_token = $token;  
		$newUser->save();

		return Helper::getResponse(['data' => ["token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => $token, 'user' => $newUser]]);
	}

	public function SubscriptionPaymentSession(Request $request)
    {
        Stripe::setApiKey('');

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'USD',
                    'product_data' => [
                        'name' => 'Subscription Payment',
                    ],
                    'unit_amount' => $request->amount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'https://gridetech.com/provider/home',
            'cancel_url' => 'https://gridetech.com/provider/signup',
        ]);

        if($session){
	        $provider = Provider::find(Auth::guard('provider')->id());
	        $provider->subscription_paid = 1;
	        $provider->save();
	    }

        return response()->json(['id' => $session->id]);
    }

	public function refresh(Request $request) {

		Auth::guard('provider')->setToken(Auth::guard('provider')->getToken());

		return Helper::getResponse(['data' => [
				"token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => Auth::guard('user')->refresh()
			]]);
	}

	public function listdocuments(Request $request) {
		$type=strtoupper($request->type);
        
		$documents = Document::with('provider_document')->where('company_id', Auth::guard('provider')->user()->company_id)->where('type',$type)->where('status',1)->get();

		return Helper::getResponse(['data' => $documents]);
	}

	public function listCarImages(Request $request, $id) {
		
		$documents = ProviderDocument::where('provider_id', Auth::guard('provider')->id())->where('vehicle_id',$id)->get();

		return Helper::getResponse(['data' => $documents]);
	}

	public function providerConsentDocument($id){
		try{
			$document = ProviderAgreeDocument::where('document_id', $id)->where('user_id', Auth::guard('provider')->id())->get();

			return Helper::getResponse(['status' => 200, 'message'=>'success','data'=>$document]);

		}catch (Exception $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }

	}

	public function updateConsentDocument(Request $request, $id){
		try {

			$document = ProviderAgreeDocument::where('document_id', $id)->where('user_id', Auth::guard('provider')->id())->get();

			if($document->count()>0){
				$newDocument = ProviderAgreeDocument::find($document[0]->id);
				$newDocument->status = $request->status;
				$newDocument->save();

				return Helper::getResponse(['message' => 'Response updated successfully.']);
			}else{
				$newDocument = new ProviderAgreeDocument();
				$newDocument->document_id = $id;
				$newDocument->user_id = Auth::guard('provider')->id();
				$newDocument->status = $request->status;
				$newDocument->save();

				return Helper::getResponse(['message' => 'Response stored successfully.']);
			}

		}catch (Exception $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
	}

	public function document_store(Request $request) {
		// echo '<pre>';print_r($request->all());exit;        
        
		$this->validate($request, [
			'file' => 'required',
			'file.*' => 'mimes:jpg,jpeg,png,pdf|max:10048',
			'expires_at'=>'required'
		],['expires_at.required' => 'Expiry Date Is Required']);

		try {
			$document = Document::find($request->document_id);

			if($document == null) {
				return Helper::getResponse(['status' => 422, 'message' => 'Document type does not exist!', 'error' => 'Document type does not exist!']);
			}

			if($request->document_id==5){
				$newcount = 0;
                foreach ($request->file('file') as $image) {
                    $newcount = $newcount+1;
                }                
                // echo'<pre>'; print_r($newcount); exit;
				// if(count($request->file('file'))!=2){//old logic
                if($newcount!=4){
					return Helper::getResponse(['status' => 422, 'message' => 'Front, Back, Left and Right side '.$document->file_type.' are required!', 'error' => 'Front, Back, Left and Right side '.$document->file_type.' are required!']);
				}
			}else{
				if($document->is_backside==1){                
	                $newcount = 0;
	                foreach ($request->file('file') as $image) {
	                    $newcount = $newcount+1;
	                }                
	                // echo'<pre>'; print_r($newcount); exit;
					// if(count($request->file('file'))!=2){//old logic
	                if($newcount!=2){
						return Helper::getResponse(['status' => 422, 'message' => 'Both Front and Back '.$document->file_type.' is required!', 'error' => 'Both Front and Back '.$document->file_type.' is required!']);
					}
				}
			}

            
			$urls = [];

			foreach ($request->file('file') as $image) {

				$file_name = time();
				$file_name .= rand();
				$file_name = sha1($file_name);

				$file_name = $file_name.'.'.$image->getClientOriginalExtension();

				$urls[]['url'] =  Helper::upload_providerfile($image, 'provider/documents', $file_name, Auth::guard('provider')->user()->company_id);
				
			}


			$providerdocuments = ProviderDocument::updateOrCreate(
				['provider_id' => Auth::guard('provider')->user()->id, 'document_id' => $request->document_id, 'vehicle_id' => $request->vehicle_id, 'company_id' => Auth::guard('provider')->user()->company_id],
				['url' => json_encode($urls), 'status' => 'ASSESSING', 'expires_at' => $request->has('expires_at') ? Carbon::parse($request->expires_at)->format('Y-m-d') : null]
			);
			$provider_total = ProviderDocument::where('provider_id', \Auth::guard('provider')->user()->id)->count();

			$providerservice=Provider::with(['providerservice' => function($query){
              $query->groupBy('admin_service');
               }])->where("id",\Auth::guard('provider')->user()->id)->get();
            
            $total=count($providerservice[0]['providerservice']);
            $document=[];
            if($total > 0){
            foreach($providerservice[0]['providerservice'] as $k =>$v){
	                $document[]=$v->admin_service;
                }
             }
            $document[]='ALL';
            $document=Document::WhereIn('type',$document)->where('status',1)->where('company_id',Auth::guard('provider')->user()->company_id)->count();
            
            $is_document=0;
            if($document==$provider_total){
			$provider=Provider::findorfail(Auth::guard('provider')->user()->id);
			$provider->is_document=1;
			$provider->save();
			 $is_document=1;
			  (new SendPushNotification)->updateProviderStatus($provider->id, 'provider', trans('admin.document_msgs.document_saved'), 'Account Info', json_encode(['service' => $provider->is_service, 'document' => $provider->is_document, 'bank' => $provider->is_bankdetail]) ); 
			}
			$providerdocuments['is_document']=$is_document;

			$admins = Admin::where('type', 'ADMIN')->get();

			$settings = json_decode(json_encode(Setting::where('company_id', Auth::guard('provider')->user()->company_id)->first()->settings_data));
            $site = $settings->site;
			
			\Log::info('Log start for notification');
			\Log::info($admins);
			\Log::info(json_encode($site));
			\Log::info(Auth::guard('provider')->user());
			\Log::info('Log end for notification');
			Notification::send($admins, new WebPushNotification($site->site_title, $site->site_icon, Auth::guard('provider')->user()->first_name. " " .Auth::guard('provider')->user()->last_name. " added new document" ));

			return Helper::getResponse(['message' => trans('admin.document_msgs.document_saved'),'data'=>$providerdocuments]);
		} catch(\Throwable $e) {
			\Log::info('Notification Exception start');
			\Log::info($e->getMessage().' in file: '.$e->getFile().' in line: '.$e->getMessage());
			\Log::info('Notification Exception end');
			return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
		}

	}

	public function logout(Request $request) {
		try {

			$User = Provider::find(\Auth::guard('provider')->id() );
			$User->is_online = 0;
			$User->jwt_token = null;			
			$User->device_token = null;
			$User->device_id = null;
			$User->device_type = null;
			$User->save();

			Auth::guard('provider')->setToken(Auth::guard('provider')->getToken());
			
			Auth::guard('provider')->invalidate();

			AuthLog::create(['user_type' => 'User', 'user_id' => \Auth::guard('provider')->id(), 'type' => 'logout', 'data' => json_encode(
				['data' => [ $request->getMethod() =>  $request->getPathInfo(). " " . $request->getProtocolVersion(), 
				'host' => $request->getHost(), 
				'user_agent' => $request->userAgent(), 
				'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]]
			)]);

			return Helper::getResponse(['message' => 'Successfully logged out']);

		} catch (JWTException $e) {

			return Helper::getResponse(['status' => 403, 'message' => $e->getMessage()]);
		}
	}

	public function forgotPasswordOTP(Request $request){
        $account_type = isset($request->account_type)?$request->account_type:'';
        if($account_type =='mobile'){
            $response =  $this->forgotPasswordMobile($request);
        }else{
            $response = $this->forgotPasswordEmail($request);
        }
        return $response;
    }

    public function forgotPasswordEmail($request) {
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'salt_key' => 'required',
        ]);
        $emaildata['username'] = $toEmail = isset($request->email)?$request->email:'';
        $emaildata['account_type'] = isset($request->account_type)?$request->account_type:'';
        try {
            $request->merge([
                'email' => $this->cusencrypt($request->email,env('DB_SECRET'))            
            ]);
            $request->request->add(['company_id' => base64_decode($request->salt_key)]);
            $request->request->remove('salt_key');
            $settings = json_decode(json_encode(Setting::where('company_id', $request->company_id)->first()->settings_data));
            $siteConfig = $settings->site;            
            $otp = mt_rand(100000, 999999);
            $userQuery = Provider::where('email' , $request->email)->first();
            //User Not Exists
            $validator  = Validator::make([],[],[]);
            if($userQuery == null) {
                $validator->errors()->add('mobile', 'User not found');
                throw new \Illuminate\Validation\ValidationException($validator); 
            }
            $userQuery->otp = $otp;
            $userQuery->save();
            $emaildata['otp'] = $otp;
            if( !empty($siteConfig->send_email) && $siteConfig->send_email == 1) {
                
                //  SEND OTP TO MAIL
					$subject='Forgot|OTP';
					$templateFile='mails/forgotpassmail';
                    $data=['body'=>$otp,'username'=>$userQuery->first_name,'salt_key'=>$request->company_id];
                    $result= Helper::send_emails($templateFile,$toEmail,$subject, $data);
                                  
                 
            }else{
                $errMessage = 'Mail configuration disabled';
            }
            return Helper::getResponse(['status' => 200, 'message'=>'success','data'=>$emaildata]);              
        }catch (Exception $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function forgotPasswordMobile($request) {
        $this->validate($request, [
            'mobile' => 'required|numeric|min:6',
            'country_code' => 'required',
        ]);
        try {
            $smsdata['country_code'] = isset($request->country_code)?$request->country_code:'';
            $smsdata['username'] = isset($request->mobile)?$request->mobile:'';
            $smsdata['account_type'] = isset($request->account_type)?$request->account_type:'';
            $plusCodeMobileNumber = '+'. $smsdata['country_code'].$smsdata['username'];
            $request->merge([
                'mobile' => $this->cusencrypt($request->mobile,env('DB_SECRET'))            
            ]);
            $request->request->add(['company_id' => base64_decode($request->salt_key)]);
            $request->request->remove('salt_key');
            $settings = json_decode(json_encode(Setting::where('company_id', $request->company_id)->first()->settings_data));
            $siteConfig = $settings->site; 
            $companyId = $request->company_id;
            $otp = mt_rand(100000, 999999);
            $userQuery = Provider::where('mobile' , $request->mobile)->first();
            //User Not Exists
            $validator  = Validator::make([],[],[]);
            if($userQuery == null) {         
                $validator->errors()->add('mobile', 'User not found');
                throw new \Illuminate\Validation\ValidationException($validator); 
            }
            $userQuery->otp = $otp;
            $saveQuery = $userQuery->save();
            if($saveQuery){
                $smsdata['otp'] = $otp;
                $smsMessage ='Your Otp to reset password is '.$otp;
                if( !empty($siteConfig->send_sms) && $siteConfig->send_sms == 1) {
                    // send OTP SMS here            
                    $result= Helper::send_sms($companyId,$plusCodeMobileNumber, $smsMessage);
                    $smsdata['smsresult']=$result;
                }else{
                    $errMessage = 'SMS configuration disabled';
                }
                return Helper::getResponse(['status' => 200, 'message'=>'success','data'=>$smsdata]);              
            }else{
                $errMessage =trans('admin.something_wrong');
            }            
        }catch (Exception $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }   
        return Helper::getResponse(['status' => 404, 'message' => $errMessage]);            
    }

    public function resetPasswordOTP(Request $request) {
        $this->validate($request, [
            'username' => 'required',
            'otp' => 'required',
            'account_type' => 'required',
            'password' => 'required|min:6|confirmed|max:36',
        ]);
        $responseData=$request->all();
        try {
            $account_type = isset($request->account_type)?$request->account_type:'';
            $username = isset($request->username)?$request->username:'';
            $newpassword = isset($request->password)?$request->password:'';
            $otp = isset($request->otp)?$request->otp:'';
            $request->merge([
                'loginUser' => $this->cusencrypt($username,env('DB_SECRET'))            
            ]);
            if($account_type =='mobile'){
                $where = ['mobile'=>$request->loginUser, 'country_code'=>$request->country_code];
            }else{
                $where = ['email'=>$request->loginUser];
            }
            $userQuery = Provider::where($where)->first();
                //User Not Exists
            $validator  = Validator::make([],[],[]);
            if($userQuery == null) {         
                $validator->errors()->add('Result', 'User not found');
                throw new \Illuminate\Validation\ValidationException($validator); 
            }else{
                $dbOtpCode = $userQuery->otp;
                if($dbOtpCode != $otp){
                    $validator->errors()->add('Result', 'Invalid Credentials');
                    throw new \Illuminate\Validation\ValidationException($validator);
                }
                $enc_newpassword = Hash::make($newpassword);
                $input =['password' => $enc_newpassword];
				$userQuery->password = $enc_newpassword;
				$userQuery->login_by = 'MANUAL';
                $userQuery->social_unique_id = NULL;
                $userQuery->otp = 0;
                $userQuery->save();
            }
            return Helper::getResponse(['status' => 200, 'message'=>'Password changed successfully','data'=>$responseData]);              
        }catch (Exception $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }            
    }

    public function verify(Request $request) {

        if($request->has('email')) {
            $request->merge([
                'email' => strtolower($request->email)
            ]);
        }

        $this->validate($request, [
            'mobile' => 'sometimes',
            'email' => 'sometimes|email|max:255',
            'salt_key' => 'required',
        ]);
    
        $company_id=base64_decode($request->salt_key);

        if($request->has('email')) {

            $request->merge([                
                'email' => $this->cusencrypt($request->email,env('DB_SECRET')),
            ]);

            $email=$request->email;

            $this->validate($request, [          
                'email' =>[ Rule::unique('providers')->where(function ($query) use($email,$company_id) {
                                return $query->where('email', $email)->where('company_id', $company_id);
                             }),
                           ],
                
            ],['email.unique'=>'User already registered with given email-Id!']);
        }    

        if($request->has('mobile')) {

            $request->merge([            
                'mobile' => $this->cusencrypt($request->mobile,env('DB_SECRET')),
            ]);

            $mobile=$request->mobile;
            $country_code=$request->country_code;

            $this->validate($request, [
                'mobile' =>[ Rule::unique('providers')->where(function ($query) use($mobile,$company_id,$country_code) {
                                return $query->where('mobile', $mobile)->where('country_code', $country_code)->where('company_id', $company_id);
                             }),
                           ],
            ],['mobile.unique'=>'User already registered with given mobile number!']);
        }

        return Helper::getResponse();
    }

}
