<?php

namespace App\Http\Controllers\V1\Order\Shop\Auth;

use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Common\AuthLog;
use App\Models\Order\Store;
use App\Models\Common\Setting;
use App\Traits\Encryptable;
use Spatie\Permission\Models\Role;
use Auth;
use DB;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    protected $jwt;
    use Encryptable;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function login(Request $request) {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        $request->merge([
            'email' => $this->cusencrypt($request->email,env('DB_SECRET'))            
        ]);

        try {

            if (! $token = Auth::guard('shop')->attempt($request->only('email', 'password'))) {
                return Helper::getResponse(['status' => 401, 'message' => 'Invalid Credentials']);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return Helper::getResponse(['status' => 500, 'message' => 'token_expired']);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return Helper::getResponse(['status' => 500, 'message' => 'token_expired']);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return Helper::getResponse(['status' => 500, 'message' => $e->getMessage()]);

        }

        $User = Store::find(Auth::guard('shop')->user()->id);
        if($User->status == 0){
            return Helper::getResponse(['status' => 422, 'message' => 'Account Disabled']);
        }
       
        $User->device_type = $request->device_type;
        $User->device_token = $request->device_token;
        $User->save();

        AuthLog::create(['user_type' => 'Shop', 'user_id' => \Auth::guard('shop')->id(), 'type' => 'login', 'data' => json_encode(
            ['data' => [ $request->getMethod() =>  $request->getPathInfo(). " " . $request->getProtocolVersion(), 
            'host' => $request->getHost(), 
            'ip' => $request->getClientIp(), 
            'user_agent' => $request->userAgent(), 
            'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]]
        )]);

        return Helper::getResponse(['data' => ["token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => $token, 'user' => Auth::guard('shop')->user()]]);

    }

    public function refresh(Request $request) {

        Auth::guard('provider')->setToken($this->getToken());

        return Helper::getResponse(['data' => [
                "token_type" => "Bearer", "expires_in" => (config('jwt.ttl', '0') * 60), "access_token" => Auth::guard('provider')->refresh()
            ]]);
    }
    public function logout(Request $request) {
        try {

            Auth::guard('shop')->setToken(Auth::guard('shop')->getToken());
            
            Auth::guard('shop')->invalidate();

            AuthLog::create(['user_type' => 'Shop', 'user_id' => \Auth::guard('shop')->id(), 'type' => 'logout', 'data' => json_encode(
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
        
        $response = $this->forgotPasswordEmail($request);
       
        return $response;
    }

    public function forgotPasswordEmail($request) {
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'salt_key' => 'required',
        ]);
        $emaildata['username'] = $toEmail = isset($request->email)?$request->email:'';
        try {
            $request->merge([
                'email' => $this->cusencrypt($request->email,env('DB_SECRET'))            
            ]);

            $company_id = base64_decode($request->salt_key);
            $request->request->add(['company_id' => base64_decode($request->salt_key)]);
            $request->request->remove('salt_key');
            $settings = json_decode(json_encode(Setting::where('company_id', $request->company_id)->first()->settings_data));
            $siteConfig = $settings->site;            
            $otp = mt_rand(100000, 999999);
            $userQuery = Store::where('email' , $request->email)->where('company_id', $company_id)->first();
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
                // if( $siteConfig->mail_driver == 'SMTP') {
                //  SEND OTP TO MAIL
                    $subject='Forgot|OTP';
                    $templateFile='mails/forgotpassmail';
                    $data=['body'=>$otp,'username'=>$userQuery->name,'salt_key'=>$request->company_id];
                    $result= Helper::send_emails($templateFile,$toEmail,$subject, $data); 
                // }else{
                //     return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => '']);  
                // }  
            }else{
                $errMessage = 'Mail configuration disabled';
            }
            return Helper::getResponse(['status' => 200, 'message'=>'success','data'=>$emaildata]);              
        }catch (Exception $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }
    }

    public function resetPasswordOTP(Request $request) {
        
        $this->validate($request, [
            'username' => 'required',
            'otp' => 'required',
            'password' => 'required|min:6',
        ]);
        $responseData = $request->all();
        try {
            $username = isset($request->username)?$request->username:'';
            $newpassword = isset($request->password)?$request->password:'';
            $otp = isset($request->otp)?$request->otp:'';
            $request->merge([
                'loginUser' => $this->cusencrypt($username,env('DB_SECRET'))            
            ]);
            
            $where = ['email'=>$request->loginUser];

            $userQuery = Store::where($where)->first();
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
                $userQuery->otp = 0;
                $userQuery->save();
            }
            return Helper::getResponse(['status' => 200, 'message'=>'Password changed successfully','data'=>$responseData]);              
        }catch (Exception $e){
            return Helper::getResponse(['status' => 404, 'message' => trans('admin.something_wrong'), 'error' => $e->getMessage()]);
        }            
    }
}
