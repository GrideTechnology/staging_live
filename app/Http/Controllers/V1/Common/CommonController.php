<?php

namespace App\Http\Controllers\V1\Common;

use App\Http\Controllers\Controller;
use App\Models\Common\Setting;
use App\Models\Common\Appsetting;
use Illuminate\Http\Request;
use App\Models\Common\AdminService;
use App\Models\Common\CompanyCountry;
use App\Services\SendPushNotification;
use App\Models\Common\CompanyCity;
use App\Models\Common\RestaurantSignup;
use App\Models\Common\Subscription;
use App\Models\Common\UserRequest;
use App\Models\Common\Company;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Common\City;
use App\Models\Common\Menu;
use App\Models\Common\CmsPage;
use App\Models\Common\Rating;
use App\Models\Common\AuthLog;
use App\Models\Common\UserWallet;
use App\Models\Common\ProviderWallet;
use App\Models\Common\FleetWallet;
use App\Models\Common\AuthMobileOtp;
use App\Models\Common\Post;
use App\Models\Common\Chat;
use App\Models\Common\AccountDeleteRequest;
use App\Helpers\Helper;
use App\Models\Common\User;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\Mail;

class CommonController extends Controller
{

    public function sendmail(Request $request)
    {
        try{

            \Log::info($request->all());

            $setting = Setting::where('company_id', 1)->first();
            $settings = json_decode(json_encode($setting->settings_data));
            $request['settings'] = $settings;

            // Mail::send('mails.enquiry',$request,function($message) use ($request) {
            //     $message->from($request->email, $request->first_name);
            //     $message->to('gayathri@appoets.com')->subject('Restaurant Signup');
            // });

            Mail::send('mails.enquiry', ['request' => $request], function ($mail) use ($request){
                    $mail->from($request->email, $request->first_name.' '.$request->last_name );
                    $mail->to('ridenowcompany@gmail.com', $request->first_name)->subject('Restaurant Signup');
               });

            return Helper::getResponse(['status' => 200, 'message' => "mail sent"]);
        }
        catch(Exception $e)
        {
            return Helper::getResponse(['status' => 500, 'message' => trans('Something Went Wrong'), 'error' => $e->getMessage() ]);
        }

        return true;

    }

    public function appsetting(){
        try{
            $setting = Appsetting::find(1)  ;
            return Helper::getResponse(['status' => 200,'data'=>$setting]);
        }
        catch (Exception $e) {
            return Helper::getResponse(['status' => 500, 'message' => trans('api.something_went_wrong'), 'error' => $e->getMessage() ]);
        }
    }
    
    public function base(Request $request) {
       
        $this->validate($request, [
            'salt_key' => 'required',
        ]);

        $license = Company::find(base64_decode($request->salt_key));
        
        if ($license != null) {
            try{  
            if (Carbon::parse($license->expiry_date)->lt(Carbon::now())) {
                return response()->json(['message' => 'License Expired'], 503);
            }

            $admin_service = AdminService::where('company_id', $license->id)->where('status', 1)->get();
           
            //$settings = Setting::where('company_id', $license->id)->first();

            $base_url = $license->base_url;

        $setting = Setting::where('company_id', $license->id)->first();
        $settings = json_decode(json_encode($setting->settings_data));
        // var_dump($settings);die;

        $company_country = CompanyCountry::with('country')->where('company_id', $license->id)->where('status', 1)->get();
       
        $appsettings=[];
        if($settings) {
         
         $appsettings['demo_mode'] = (int)$setting->demo_mode;
         $appsettings['country'] = (int)$settings->site->country;
         $appsettings['background_check_enable'] = $settings->site->store_link_company_link_enable;
         $appsettings['background_check_link'] = $settings->site->store_link_company_link;
         $appsettings['zego_app_id'] = $settings->site->zego_app_id;
         $appsettings['zego_app_sign'] = $settings->site->zego_app_sign;
         $appsettings['provider_negative_balance'] = (isset($settings->site->provider_negative_balance)) ? $settings->site->provider_negative_balance : '';
         $appsettings['android_key'] = (isset($settings->site->android_key)) ? $settings->site->android_key : '';
         $appsettings['ios_key'] = (isset($settings->site->ios_key)) ? $settings->site->ios_key : '';
         $appsettings['referral'] = ($settings->site->referral ==1) ? 1 : 0;
        
         $appsettings['social_login'] = ($settings->site->social_login ==1) ? 1 :0;
         $appsettings['send_sms'] = ($settings->site->send_sms == 1) ? 1 : 0;
         $appsettings['send_email'] = ($settings->site->send_email == 1) ? 1 : 0;
         $appsettings['otp_verify'] = ($settings->transport->ride_otp == 1) ? 1 : 0;
         
         $appsettings['ride_otp'] = ($settings->transport->ride_otp == 1) ? 1 : 0;
         $appsettings['provider_accept_time'] = ($settings->order->provider_select_timeout !='') ? $settings->order->provider_select_timeout : 60;
         
         $appsettings['order_otp'] = ($settings->order->order_otp == 1) ? 1 : 0;
         $appsettings['date_format'] = (isset($settings->site->date_format)) ? $settings->site->date_format : 0;
       
        
         $appsettings['service_otp'] = ($settings->service->serve_otp == 1) ? 1 : 0;         
         $appsettings['payments'] = (count($settings->payment) > 0) ? $settings->payment : 0;
         
         $appsettings['cmspage']['privacypolicy'] = (isset($settings->site->page_privacy)) ? $settings->site->page_privacy : 0;
         $appsettings['cmspage']['help'] = (isset($settings->site->help)) ? $settings->site->help : 0;
         $appsettings['cmspage']['terms'] = (isset($settings->site->terms)) ? $settings->site->terms : 0;
         $appsettings['cmspage']['cancel'] = (isset($settings->site->cancel)) ? $settings->site->cancel : 0;
         $appsettings['supportdetails']['contact_number'] = (isset($settings->site->contact_number) > 0) ? $settings->site->contact_number : 0;
         $appsettings['supportdetails']['contact_email']=(isset($settings->site->contact_email) > 0) ? $settings->site->contact_email : 0;
         $appsettings['languages']=(isset($settings->site->language) > 0) ? $settings->site->language : 0;
        
        }
              return Helper::getResponse(['status' => 200, 'data' => ['base_url' => $base_url, 'services' => $admin_service,'appsetting'=>$appsettings, 'country' => $company_country ]]);
            }catch (Exception $e) {
               
                return Helper::getResponse(['status' => 500, 'message' => trans('Something Went Wrong'), 'error' => $e->getMessage() ]);
            }
        }
    }

    public function admin_services() {

        $admin_service = AdminService::where('company_id', Auth::user()->company_id)->whereNotIn('admin_service', ['ORDER'] )->where('status', 1)->get();

        return Helper::getResponse(['status' => 200, 'data' => $admin_service]);

    }

    public function countries_list() {
        $countries = Country::get();
        return Helper::getResponse(['data' => $countries]);
    }

    public function states_list($id) {
        $states = State::where('country_id', $id)->get();
        return Helper::getResponse(['data' => $states]);
    }

    public function cities_list($id) {
        $cities = City::where('state_id', $id)->get();
        return Helper::getResponse(['data' => $cities]);
    }

    public function cmspagetype($type) {
        $cities = CmsPage::where('page_name', $type)->get();
        // print_r($cities);die;
        return Helper::getResponse(['data' => $cities]);
    }
    public function bloglist(Request $request) {
        $blogs = Post::whereDate('time', '<=', Carbon::now());
        if(!empty($request->searchData)) {
            $blogs = $blogs->where('title','LIKE','%'.$request->searchData.'%');
        }
        $total = $blogs->count();
        $blogs = $blogs->orderBy('id', 'DESC')->offset($request->skip)->limit($request->limit)->get();
        if(!empty($blogs)) {
            foreach($blogs as $value) {
                $value->time =  Carbon::parse($value->time)->format('F d, Y');
                $value->content = $this->limit_text($value->content,20);
                $value->blog_image = $value->image;
            }
        }
        $data['blogs'] = $blogs;
        $data['total'] = $total;
        return Helper::getResponse(['data' => $data]);
    }
    public function singleBlog($slug) {
        $datum = Post::where('slug',$slug)->first();
        return Helper::getResponse(['data' => $datum]);
    }
    public function limit_text($text, $limit) {
        if (str_word_count($text, 0) > $limit) {
            $words = str_word_count($text, 2);
            $pos   = array_keys($words);
            $text  = substr($text, 0, $pos[$limit]) . '...';
        }
        return $text;
    }

    public function rating($request) {

        Rating::create([
                    'company_id' => $request->company_id,
                    'admin_service' => $request->admin_service,
                    'provider_id' => $request->provider_id,
                    'user_id' => $request->user_id,
                    'request_id' => $request->id,
                    'user_rating' => $request->rating,
                    'user_comment' => $request->comment,
                  ]);

        return true;
    }

    public function logdata($type, $id)
    {
        
        $date = \Carbon\Carbon::today()->subDays(7);

        $datum = AuthLog::where('user_type', $type)->where('user_id', $id)->orderBy('created_at','DESC')->whereDate('created_at', '>', $date)->paginate(5);

        return Helper::getResponse(['data' => $datum]);
    }

    public function walletDetails($type, $id)
    {
        
        $date = \Carbon\Carbon::today()->subDays(15);

        if($type == "User"){
            $datum = UserWallet::with('user')->where('user_id', $id)->select('*',\DB::raw('DATEDIFF(now(),created_at) as days'),\DB::raw('TIMEDIFF(now(),created_at) as total_time'));

        }elseif ($type == "Provider") {
            $datum = ProviderWallet::with('provider')->where('provider_id', $id);
        }elseif ($type == "Fleet") {
            $datum = FleetWallet::with('provider')->where('fleet_id', $id);
        }else if($type == "store"){
            try{ 
            $datum =\App\Models\Order\StoreWallet::where('store_id',$id);
            }catch (Exception $e) {
              return Helper::getResponse(['data' => []]);
                
            } 
        }

        $wallet_details = $datum->orderBy('created_at','DESC')->whereDate('created_at', '>', $date)->paginate(10);

        return Helper::getResponse(['data' => $wallet_details]);
    }

    public function chat(Request $request) 
    {
        
        $this->validate($request,[
            'id' => 'required',
            'admin_service' => 'required|in:TRANSPORT,ORDER,SERVICE', 
            'salt_key' => 'required',
            'user_name' => 'required',
            'provider_name' => 'required',
            'type' => 'required',
            'message' => 'required'
        ]);

        $company_id = base64_decode($request->salt_key);

        $user_request = UserRequest::where('request_id', $request->id)->where('admin_service', $request->admin_service)->where('company_id', $company_id)->first();

        if($user_request != null) {
            $chat=Chat::where('admin_service', $request->admin_service)->where('request_id', $request->id)->where('company_id', $company_id)->first();


            if($chat != null) {
                $data = $chat->data;
                $data[] = ['type' => $request->type, 'user' => $request->user_name, 'provider' => $request->provider_name, 'message' => $request->message  ];
                $chat->data = json_encode($data);
                $chat->save();
            } else {
                $chat = new Chat();
                $data[] = ['type' => $request->type, 'user' => $request->user_name, 'provider' => $request->provider_name, 'message' => $request->message  ];
                $chat->admin_service = $request->admin_service;
                $chat->request_id = $request->id;
                $chat->company_id = $company_id;
                $chat->data = json_encode($data);
                $chat->save();
            }

            if($request->type == 'user') {                                                         
                (new SendPushNotification)->ChatPushProvider($user_request->provider_id, 'chat_'.strtolower($chat->admin_service)); 
            } else if($request->type == 'provider') {                       
                (new SendPushNotification)->ChatPushUser($user_request->user_id, 'chat_'.strtolower($chat->admin_service)); 
            }
            
            

            return Helper::getResponse(['message' => 'Successfully Inserted!']);
        } else {
            return Helper::getResponse(['status' => 400, 'message' => 'No service found!']);
        }

        
    }


    public function goonline(Request $request){        
        
        $this->validate($request,[
			'is_online' => 'required|integer',			
            'user_id' => 'required|integer',            
		]);
        try{            
                        
            User::where('id',$request->user_id)->update([
                "is_online" => $request->is_online
            ]);
            return Helper::getResponse(['status' => 200, 'message' => trans('admin.update')]);
        } catch(\Exception $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function sendOtp(Request $request) {

        $this->validate($request, [
            'country_code' => 'required',
            'mobile' => 'required',
            'salt_key' => 'required',
        ]);


        $company_id=base64_decode($request->salt_key);

        $otp = $this->createOtp($company_id);
        // $otp = '1234';

        AuthMobileOtp::updateOrCreate(['company_id' => $company_id, 'country_code' => $request->country_code, 'mobile' => $request->mobile],['otp' => $otp]);
        //return Helper::getResponse(['message' => 'OTP sent!']);
        
        $send_sms = Helper::send_sms($company_id, '+'.$request->country_code.''.$request->mobile, 'Your OTP is ' . $otp . '. Do not share your OTP with anyone' );

        if($send_sms === true) {
            return Helper::getResponse(['message' => 'OTP sent!']);
        } else {
            return Helper::getResponse(['status' => '400', 'message' => 'Could not send SMS notification. Please try again!', 'error' => $send_sms]);
        }

        
    }

    public function createOtp($company_id) {

        $otp = mt_rand(1111, 9999);

        $auth_mobile_otp = AuthMobileOtp::select('id')->where('otp', $otp)->where('company_id', $company_id)->orderBy('id', 'desc')->first();

        if($auth_mobile_otp != null) {
            $this->createOtp($company_id);
        } else {
            return $otp ;
        } 
    }

    public function verifyOtp(Request $request) {

        $this->validate($request, [
            'country_code' => 'required',
            'mobile' => 'required',
            'otp' => 'required',
            'salt_key' => 'required',
        ]);


        $company_id=base64_decode($request->salt_key);

        $auth_mobile_otp = AuthMobileOtp::where('country_code', $request->country_code)->where('mobile', $request->mobile)->where('otp', $request->otp)->where('updated_at','>=',Carbon::now()->subMinutes(10))->where('company_id', $company_id)->first();

        if($auth_mobile_otp != null) {

            $auth_mobile_otp->delete();

            return Helper::getResponse([ 'message' => 'OTP verified!' ]);
        } else {

            return Helper::getResponse([ 'status' => '400', 'message' => 'OTP error!' ]);

        }   
    }

    public function deleteLog() {

        $api = storage_path('logs/lumen.log');        
        $files = [$api];        

        foreach ($files as $file) {            
            file_put_contents($file, print_r(json_encode(['datetime' => date("Y-m-d H:i:s"), 'log' => '']), TRUE), FILE_SKIP_EMPTY_LINES);       
        }
        
    }

    public function set_stripe(){

        $settings = json_decode(json_encode(Setting::where('company_id', 1)->first()->settings_data));

        $paymentConfig = json_decode( json_encode( $settings->payment ) , true);;

        $cardObject = array_values(array_filter( $paymentConfig, function ($e) { return $e['name'] == 'card'; }));
        $card = 0;

        $stripe_secret_key = "";
        $stripe_publishable_key = "";
        $stripe_currency = "";

        if(count($cardObject) > 0) { 
            $card = $cardObject[0]['status'];

            $stripeSecretObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_secret_key'; }));
            $stripePublishableObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_publishable_key'; }));
            $stripeCurrencyObject = array_values(array_filter( $cardObject[0]['credentials'], function ($e) { return $e['name'] == 'stripe_currency'; }));

            if(count($stripeSecretObject) > 0) {
                $stripe_secret_key = $stripeSecretObject[0]['value'];
            }

            if(count($stripePublishableObject) > 0) {
                $stripe_publishable_key = $stripePublishableObject[0]['value'];
            }

            if(count($stripeCurrencyObject) > 0) {
                $stripe_currency = $stripeCurrencyObject[0]['value'];
            }
        }


        return \Stripe\Stripe::setApiKey( $stripe_secret_key );
    }

    public function customer_id($email)
    {
        try{ 
            $stripe = $this->set_stripe();
            $customer = \Stripe\Customer::create([
                'email' => $email,
            ]);

            
            return $customer['id'];

        } catch(Exception $e){
            return $e;
        }
    }

    function createPaymentMethod($cardDetails) {
        try {
            $paymentMethod = \Stripe\PaymentMethod::create([
                'type' => 'card',
                'card' => [
                    'number' => $cardDetails['number'],
                    'exp_month' => $cardDetails['exp_month'],
                    'exp_year' => $cardDetails['exp_year'],
                    'cvc' => $cardDetails['cvc'],
                ],
            ]);
            return $paymentMethod->id;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return false;
        } catch (\Exception $e) {
            // Catch other general errors
            return false;
        }
    }

    function attachPaymentMethodToCustomer($paymentMethodId, $customerId) {
        $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->attach(['customer' => $customerId]);

        // Optionally, you can set this payment method as the default payment method
        \Stripe\Customer::update($customerId, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodId,
            ],
        ]);

        return $paymentMethod;
    }

    function createCharge($customerId, $method_id, $amount, $currency = 'usd') {
        $charge = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'customer' => $customerId,
            'payment_method' => $method_id,
            'confirm' => true,
            'return_url' => "https://gridetech.com/user/signup"

        ]);
        return $charge;
    }

    public function RestaurantSignup(Request $request){
         $this->validate($request,[
            'first_name_new' => 'required',
            'last_name_new' => 'required', 
            'email_new' => 'required',
            'phone_new' => 'required',
            'service_radio' => 'required',
            //'size' => 'required',
            'phone_code_new'=> 'required',
            'restro_radio' => 'required',
            'restro_name' => 'required',
            'restro_address' => 'required', 
            'restro_phone' => 'required',
            'restro_company_name' => 'required',
            'cars_type' => 'required',
            'tax_company_name' => 'required',
            'tax_FEIN' => 'required',
            'payment_firstname' => 'required',
            'payment_lastname' => 'required',
            'payment_bankname' => 'required',
            'acc_number' => 'required',
            'payment_ach_number' => 'required',
            'subscription' => 'required',
            'card_name' => 'required',
            'card_num' => 'required',
            'card_cvv' => 'required',
            'card_expmonth' => 'required',
            'card_expyear' => 'required'
        ]);

        try{
            $restaurant = new RestaurantSignup;
            $restaurant->first_name_new = $request->first_name_new;
            $restaurant->last_name_new = $request->last_name_new;
            $restaurant->email_new = $request->email_new;
            $restaurant->phone_new = $request->phone_new;
            $restaurant->phone_code_new = $request->phone_code_new;
            $restaurant->service_radio = $request->service_radio;
            $restaurant->size = $request->size;
            $restaurant->restro_radio = $request->restro_radio;
            $restaurant->restro_address = $request->restro_address;
            $restaurant->restro_name = $request->restro_name;
            $restaurant->restro_phone = $request->restro_phone;
            $restaurant->restro_company_name = $request->restro_company_name;
            $restaurant->cars_type = $request->cars_type;
            $restaurant->tax_company_name = $request->tax_company_name;
            $restaurant->tax_FEIN = $request->tax_FEIN;
            $restaurant->payment_firstname = $request->payment_firstname;
            $restaurant->payment_lastname = $request->payment_lastname;
            $restaurant->payment_bankname = $request->payment_bankname;
            $restaurant->acc_number = $request->acc_number;
            $restaurant->payment_ach_number = $request->payment_ach_number;  
            $restaurant->subscription_id = $request->subscription;  

            $cardDetails = array();
            $cardDetails['number'] = $request->card_num;
            $cardDetails['exp_month'] = $request->card_expmonth;
            $cardDetails['exp_year'] = $request->card_expyear;
            $cardDetails['cvc'] = $request->card_cvv;
            $subscription = Subscription::find($request->subscription);
            $amount = round($subscription->amount * 100);

            $customer_id = $this->customer_id($request->email_new);
            $card_id = $this->createPaymentMethod($cardDetails);
            if($card_id){
                $method_id = $this->attachPaymentMethodToCustomer($card_id, $customer_id);
                $charge = $this->createCharge($customer_id, $method_id, $amount);
                if (!empty($charge->id)) {
                    $restaurant->save();
                    return Helper::getResponse(['status' => 200, 'message' => 'Restaurant Created']);
                } else {
                    throw new Exception('Something went wrong.');
                }
            }else{
                 throw new Exception('Something went wrong.');
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle Stripe API errors
            return Helper::getResponse(['status' => 422, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        } catch (Exception $e) {
            // Handle other exceptions
            return Helper::getResponse(['status' => 422, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }

    }


    public function contact(Request $request){
        $validate_fields['name'] = 'required';
        $validate_fields['email'] = 'required';
        $validate_fields['message'] = 'required';
        
        if(!isset($request->type)){
            $validate_fields['mobile'] = 'required';
        }else{
            $validate_fields['username'] = 'required';
        }
        

        $this->validate($request,$validate_fields);
        
        try{
            $query = new AccountDeleteRequest;
            $query->name = $request->name;
            $query->email = $request->email;
            if(isset($request->username)){
                $query->username = $request->username;
            }
            if(isset($request->mobile)){
                $query->mobile = $request->mobile;
            }
            $query->message = $request->message;
            if(isset($request->consent)){
                $query->consent = $request->consent;
            }

            if(isset($request->type)){
                $query->type = $request->type;
            }
            
            $query->save();

            return Helper::getResponse(['status' => 200, 'message' => 'Request Submitted successfully.']);
        } catch (Exception $e) {
            // Handle other exceptions
            return Helper::getResponse(['status' => 404, 'message' => $e->getMessage(), 'error' => $e->getMessage()]);
        } 
    }
}
