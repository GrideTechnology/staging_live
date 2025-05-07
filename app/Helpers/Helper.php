<?php
namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\Common\RequestLog;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;
use App\Models\Common\AdminService;
use App\Models\Common\Setting;
use App\Services\FirebaseService;
use Auth;
use Illuminate\Support\Facades\Crypt; 
use Log;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Illuminate\Support\Facades\Mail;


use function App\Providers\asset;

class Helper {


	public static function getUsername(Request $request) {
		
		$username = "";

		if(isset($request->mobile)) {
			$username = 'mobile';
		} else if(isset($request->email)) {
			$username = 'email';
		}

		return $username;
	}

	public static function dateFormat($company_id=null){
		$setting = Setting::where('company_id', 1)->first();
		$settings = json_decode(json_encode($setting->settings_data));
		$siteConfig = isset($settings->site->date_format) ? $settings->site->date_format:0 ;
		if($siteConfig=='1'){
          return "d-m-Y H:i:s";
		}else{
          return "d-m-Y g:i A";
		}
	}

	public static function currencyFormat($value = '',$symbol='')
	{
		if($value == ""){
			return $symbol.number_format(0, 2, '.', '');
		} else {
			return $symbol.number_format($value, 2, '.', '');
		}
	}

	public static function decimalRoundOff($value)
	{
		return number_format($value, 2, '.', '');
	}

	public static function qrCode($data, $file, $company_id, $path = 'qr_code/', $size = 500, $margin = 10) {
		//return true;
		$qrCode = new QrCode();
        $qrCode->setText($data);
        $qrCode->setSize($size);
        $qrCode->setWriterByName('png');
        $qrCode->setMargin($margin);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));

        $qrCode->setRoundBlockSize(true);
        $qrCode->setValidateResult(false);
        $qrCode->setWriterOptions(['exclude_xml_declaration' => true]);
        $filePath = 'app/public/'.$company_id.'/'.$path;
		
		$filePath = 'app/public/'.$company_id.'/'.$path;

        if (!file_exists( app()->basePath('storage/'.$filePath )  )) {
            mkdir(app()->basePath('storage/'.$filePath ), 0777, true);
        }

        $qrCode->writeFile( app()->basePath('storage/'.$filePath ).$file);

		$url = str_replace('/public', '', url());
        return $url.'/storage/'.$company_id.'/'.$path.$file; 

	}

	public static function upload_file($picture, $path, $file = null, $company_id = null)
	{
		if($file == null) {
			$file_name = time();
			$file_name .= rand();
			$file_name = sha1($file_name);

			$file = $file_name.'.'.$picture->getClientOriginalExtension();
		}
		
		if(!empty(Auth::user())){          
            $company_id = Auth::user()->company_id;
        }

		$path = $company_id.'/'.$path;
		
		if (!file_exists( app()->basePath('storage/app/public/'.$path )  )) {
            mkdir(app()->basePath('storage/app/public/'.$path ), 0777, true);
        }

		$url = str_replace('/public', '', url());		
        return $url.'/storage/app/public/'.$picture->storeAs($path, $file);
	}

	public static function upload_providerfile($picture, $path, $file = null, $company_id = null)
	{
		try{
			if($file == null) {
				$file_name = time();
				$file_name .= rand();
				$file_name = sha1($file_name);

				$file = $file_name.'.'.$picture->getClientOriginalExtension();
			}

			$path = ( ($company_id == null) ? Auth::guard('provider')->user()->company_id : $company_id ) .'/'.$path;
			
			if (!file_exists( app()->basePath('storage/app/public/'.$path )  )) {
				mkdir(app()->basePath('storage/app/public/'.$path ), 0777, true);
			}
			$url = str_replace('/public', '', url());
			return $url.'/storage/app/public/'.$picture->storeAs($path, $file);
		} catch(\Exception $e){
			\Log::info('getting error in provider doc upload');
			\Log::info($e->getMessage());			
			return ['responseCode' => 400, 'responseMessage' => $e->getMessage(). ' in file '.$e->getFile(). ' at line '.$e->getLine()];
		}
	}

	public static function getGuard(){
	    if(Auth::guard('admin')->check()) {
	    	return strtoupper("admin");
	    } else if(Auth::guard('provider')->check()) {
	    	return strtoupper("provider");
	    } else if(Auth::guard('user')->check()) {
	    	return strtoupper("user");
	    } else if(Auth::guard('shop')->check()){
	    	return strtoupper("shop");
	    }
	}

	public static function curl($url)
	{
		// return $url;
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    $return = curl_exec($ch);
	    curl_close ($ch);
	    return $return;


		// $curl = curl_init();

		// curl_setopt_array($curl, array(
		// 	CURLOPT_URL => $url,
		// 	CURLOPT_RETURNTRANSFER => true,
		// 	CURLOPT_ENCODING => "",
		// 	CURLOPT_MAXREDIRS => 10,
		// 	CURLOPT_TIMEOUT => 30,
		// 	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// 	CURLOPT_CUSTOMREQUEST => "GET",
		// 	CURLOPT_HTTPHEADER => array(
		// 	"cache-control: no-cache",
		// 	),
		// ));

		// $response = curl_exec($curl);
		// $err = curl_error($curl);
		// curl_close($curl);

		// return $response;
	}

	public static function generate_booking_id($prefix) {
		return $prefix.mt_rand(100000, 999999);
	}

	public static function setting($company_id = null)
	{
		
		if( Auth::guard(strtolower(self::getGuard()))->user() != null ) {
			$id = ($company_id == null) ? Auth::guard(strtolower(self::getGuard()))->user()->company_id : $company_id;
		} else {
			$id = 1;
		}
		$setting = Setting::where('company_id', $id )->first();          
		$settings = json_decode(json_encode($setting->settings_data));

		$settings->demo_mode = $setting->demo_mode;
		return $settings;
	}

	public static function getAddress($latitude,$longitude){

		if(!empty($latitude) && !empty($longitude)){
			//Send request and receive json data by address
			$geocodeFromLatLong = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($latitude).','.trim($longitude).'&sensor=false&key='.config('constants.map_key')); 
			$output = getDistanceMap(trim($latitude), trim($longitude));
			$status = $output->status;
			//Get address from json data
			$address = ($status=="OK")?$output->results[0]->formatted_address:'';
			//Return address of the given latitude and longitude
			if(!empty($address)){
				return $address;
			}else{
				return false;
			}
		}else{
			return false;   
		}
	}

	public static function getDistanceMap($source, $destination) {

		$settings = Helper::setting();
		$siteConfig = $settings->site;

		$map = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?origins='.implode('|', $source).'&destinations='.implode('|', $destination).'&sensor=false&key='.$siteConfig->server_key); 
		return json_decode($map);
	}

	public static function my_encrypt($passphrase, $encrypt) {
	 
	    $salt = openssl_random_pseudo_bytes(128);
		$iv = openssl_random_pseudo_bytes(16);
		//on PHP7 can use random_bytes() istead openssl_random_pseudo_bytes()
		//or PHP5x see : https://github.com/paragonie/random_compat

		$iterations = 999;  
		$key = hash_pbkdf2("sha1", $passphrase, $salt, $iterations, 64);

		$encrypted_data = openssl_encrypt($encrypt, 'aes-128-cbc', hex2bin($key), OPENSSL_RAW_DATA, $iv);

		$data = array("ciphertext" => base64_encode($encrypted_data), "iv" => bin2hex($iv), "salt" => bin2hex($salt));		
		return $data;

	}

	public static function encryptResponse($response = []) {
		
		$status = !empty($response['status']) ? $response['status'] : 200 ;
		$title = !empty($response['title']) ? $response['title'] : self::getStatus($status) ;
		$message = !empty($response['message']) ? $response['message'] : '' ;	
		$responseData = !empty($response['data']) ? self::my_encrypt('FbcCY2yCFBwVCUE9R+6kJ4fAL4BJxxjd', json_encode($response['data'])) : [] ;
		$error = !empty($response['error']) ? $response['error'] : [] ;

		if( ($status != 401) && ($status != 405) && ($status != 422)  ) {

			RequestLog::create(['data' => json_encode([
			'request' => app('request')->request->all(),
			'response' => $message,
			'error' => $error,
			'responseCode' => $status,
			$_SERVER['REQUEST_METHOD'] => $_SERVER['REQUEST_URI'] . " " . $_SERVER['SERVER_PROTOCOL'], 
            'host' => $_SERVER['HTTP_HOST'], 
            'ip' => $_SERVER['REMOTE_ADDR'], 
            'user_agent' => $_SERVER['HTTP_USER_AGENT'], 
            'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')])]);

		}
		
		return response()->json(['statusCode' => (string) $status, 'title' => $title, 'message' => $message, 'responseData' => $responseData, 'error' => $error], $status);
	}

	public static function getResponse($response = []) {		
		$status = !empty($response['status']) ? $response['status'] : 200 ;
		$title = !empty($response['title']) ? $response['title'] : self::getStatus($status) ;
		$message = !empty($response['message']) ? $response['message'] : '' ;
		$responseData = !empty($response['data']) ? $response['data'] : [] ;
		$error = !empty($response['error']) ? $response['error'] : [] ;

		if( ($status != 401) && ($status != 405) && ($status != 422)  ) {
		
			app('request')->request->remove('picture');
			app('request')->request->remove('file');
			app('request')->request->remove('vehicle_image');
			app('request')->request->remove('vehicle_marker');

			RequestLog::create(['data' => json_encode([
			'request' => app('request')->request->all(),
			'response' => $message,
			'error' => $error,
			'responseCode' => $status,
			$_SERVER['REQUEST_METHOD'] => $_SERVER['REQUEST_URI'] . " " . $_SERVER['SERVER_PROTOCOL'], 
            'host' => $_SERVER['HTTP_HOST'], 
            'ip' => $_SERVER['REMOTE_ADDR'], 
            'user_agent' => $_SERVER['HTTP_USER_AGENT'], 
            'date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')])]);

		}		
		return response()->json(['statusCode' => (string) $status, 'title' => $title, 'message' => $message, 'responseData' => $responseData, 'error' => $error], $status);
	}

	public static function getStatus($code) {

		switch ($code) {
			case 200:
				return "OK";
				break;
			
			case 201:
				return "Created";
				break;

			case 204:
				return "No Content";
				break;

			case 301:
				return "Moved Permanently";
				break;

			case 400:
				return "Bad Request";
				break;

			case 401:
				return "Unauthorized";
				break;

			case 403:
				return "Forbidden";
				break;

			case 404:
				return "Not Found";
				break;

			case 405:
				return "Method Not Allowed";
				break;

			case 422:
				return "Unprocessable Entity";
				break;

			case 500:
				return "Internal Server Error";
				break;

			case 502:
				return "Bad Gateway";
				break;

			case 503:
				return "Service Unavailable";
				break;
		}
	}


	public static function delete_picture($picture) {
		$url = app()->basePath('storage/') . $picture;
		@unlink($url);
		return true;
	}

	public static function send_sms($companyId,$plusCodeMobileNumber, $smsMessage) {
		//  SEND OTP TO REGISTER MEMBER
		$settings = json_decode(json_encode(Setting::where('company_id',$companyId)->first()->settings_data));
		$siteConfig = $settings->site; 
		$accountSid =$siteConfig->sms_account_sid;
		$authToken = $siteConfig->sms_auth_token;
		$twilioNumber = $siteConfig->sms_from_number;		
		
		$client = new Client($accountSid, $authToken);
		// $tousernumber = '+17577932902';
		$tousernumber = $plusCodeMobileNumber ;
		try {
			$client->messages->create(
				$tousernumber,
				[
					"body" => $smsMessage,
					"from" => $twilioNumber
					//   On US phone numbers, you could send an image as well!
					//  'mediaUrl' => $imageUrl
				]
			);
			Log::info('Message sent to ' . $plusCodeMobileNumber.'from '. $twilioNumber);
			return true;
		} catch (TwilioException $e) {
			Log::error(
				'Could not send SMS notification.' .
				' Twilio replied with: ' . $e
			);
			return $e->getMessage();
		}

	}

	public static function siteRegisterMail($user){

		try{

            $settings = json_decode(json_encode(Setting::where('company_id',$user->company_id)->first()->settings_data));    
                        
            // Mail::send('mails.welcome',['user' => $user, 'settings' => $settings],function($message) use($to,$subject){
            //     $message->to($to,$subject)->subject($subject);
            //     $message->from(env('MAIL_USERNAME'));
            // });
            // $sendresponse = Mail::send('mails.welcome', ['user' => $user, 'settings' => $settings], function ($mail) use ($user, $settings) {
            //     // $mail->from($settings->site->mail_from_address, $settings->site->mail_from_name);                
            //     $mail->to($user->email,'Welcome')->subject('Welcome');
            //     $mail->from(env('MAIL_FROM_ADDRESS'),env('MAIL_FROM_NAME'));
            // });            
            return true;
		}
		catch (\Throwable $e) {	
			\Log::info($e);
		
            throw new \Exception($e->getMessage());
        }
	}
	
	public static function send_emails($templateFile,$toEmail,$subject, $data) {
		try{
			\Log::info($templateFile);
			//dd($data['salt_key']);
            if(isset($data['salt_key'])){
				$settings = json_decode(json_encode(Setting::where('company_id',$data['salt_key'])->first()->settings_data));
			}else{
                   if(!empty(Auth::user())){          
			            $company_id = Auth::user()->company_id;
			        }
			        else if(!empty(Auth::guard('shop')->user())){          
			            $company_id = Auth::guard('shop')->user()->company_id;
			        }else{

			        }
				$settings = json_decode(json_encode(Setting::where('company_id',$company_id)->first()->settings_data));
			}
			$data['settings'] = $settings;
			$mail =  Mail::send($templateFile,$data,function($message) use ($data,$toEmail,$subject,$settings) {
				$message->from($settings->site->mail_from_address, $settings->site->mail_from_name);
				$message->to($toEmail)->subject($subject);
			});
			
			if( count(Mail::failures()) > 0 ) {
			  
			   throw new \Exception('Error: Mail sent failed!');

			} else {
				return true;
			}
			
		}
		catch (\Throwable $e) {	
			\Log::info($e);
		
            throw new \Exception($e->getMessage());
        } 
		
	}

	
	public static function send_emails_job($templateFile, $toEmail, $subject, $data) 
	{
		try{
			
			$mail =  Mail::send($templateFile, $data, function($message) use ($data, $toEmail, $subject) {
				$message->from("dev@appoets.com", "GOX");
				$message->to($toEmail)->subject($subject);
			});

			// dd(Mail::failures());
			
			if( count(Mail::failures()) > 0 ) {
			  
			   throw new \Exception('Error: Mail sent failed!');

			} else {
				return true;
			}
			
		}
		catch (\Throwable $e) {	
			dd($e);
		
            throw new \Exception($e->getMessage());
        } 
		
	}

	/*
      Send push to Ios
     */

    public static function sendPushIOS($registrationId, $msgData,$settings) {
    	$fields['message']['token'] = $registrationId;
        $fields['message']['notification'] = [
            'title' => $msgData['title'],
            'body' => $msgData['body'],
            // 'badge' => $msgData['badge'],
            // 'sound' => !empty($msgData['sound']) ? $msgData['sound'] : 'default',
            // 'icon' => '',//Yii::$app->params['LOGO_URL']

        ];
        $fields['message']['data'] = [
            'type' => $msgData['type'],
            'id' => !empty($msgData['id']) ? $msgData['id'] : '',
        ];


        if(array_key_exists('ride_id',$msgData)){
        	$fields['message']['data']['ride_id'] = isset($msgData['ride_id']) ? strval($msgData['ride_id']): null;
        }

        if(array_key_exists('order_id',$msgData)){
        	$fields['message']['data']['order_id'] = isset($msgData['order_id']) ? strval($msgData['order_id']): null;
        }
        
        return Helper::pushCurlCall($registrationId, $fields,$settings);
    }

    /*
      Send push to Android
     */

    public static function sendPushAndroid($registrationId, $msgData,$settings) {
        /*$fields['data'] = [
            'title' => $msgData['title'],
            'body' => $msgData['body'],
            'badge' => !empty($msgData['badge']) ? $msgData['badge'] : 0,
            'sound' => !empty($msgData['sound']) ? $msgData['sound'] : 'default',
            'icon' => '',//Yii::$app->params['LOGO_URL']
            'type' => $msgData['type'],
            'id' => !empty($msgData['iUserId']) ? $msgData['iUserId'] : '',
        ];
        $fields['notification'] = [
            'title' => $msgData['title'],
            'body' => $msgData['body'],
            'badge' => !empty($msgData['badge']) ? $msgData['badge'] : 0,
            'sound' => !empty($msgData['sound']) ? $msgData['sound'] : 'default',
            'icon' => '',//Yii::$app->params['LOGO_URL']
            'type' => $msgData['type'],
            'id' => !empty($msgData['iUserId']) ? $msgData['iUserId'] : '',
        ];  */

        $fields['message']['token'] = $registrationId;
        $fields['message']['notification'] = [
            'title' => $msgData['title'],
            'body' => $msgData['body'],
            // 'badge' => $msgData['badge'],
            // 'sound' => !empty($msgData['sound']) ? $msgData['sound'] : 'default',
            // 'icon' => '',//Yii::$app->params['LOGO_URL']

        ];
        $fields['message']['data'] = [
            'type' => $msgData['type'],
            'id' => !empty($msgData['id']) ? $msgData['id'] : '',
        ];


        if(array_key_exists('ride_id',$msgData)){
        	$fields['message']['data']['ride_id'] = isset($msgData['ride_id']) ? strval($msgData['ride_id']): null;
        }

        if(array_key_exists('order_id',$msgData)){
        	$fields['message']['data']['order_id'] = isset($msgData['order_id']) ? strval($msgData['order_id']): null;
        }
        

        return Helper::pushCurlCall($registrationId, $fields,$settings);
    }

    //Old Send push notification
    /*public static function sendPushWeb($registrationId, $msgData,$settings){
    	$SERVER_API_KEY = 'AAAAiLO3YWY:APA91bHuPa6lr94gTaKZ0w7WcJ3IYNsTNSGctH024Luh1i3LJ8PphG6OunXYySps4MEDeiSE-1R2iDPiPurx-JX58iZsGug5o9NzDBMo7xp12fqBSNxO7hsPbef1t1yCd3TqDDYli1Hg';
   
        $URL = 'https://fcm.googleapis.com/fcm/send';

        $fields =array(
	        "notification"=> array(
	                      "title" =>  'testin web',
	                      "body" =>  'Web tetsing shop', //Can be any message you want to send
	                      "icon" => 'https://api.gridetech.com/storage/app/public/1/site/site_logo.png',
	                      "sound" => "default"
	                      ),
	                  "registration_id"=> $registrationId,
	                  "data" => array(
	                      "body" => 'Web tetsing shop',
	                      "title" => 'testting web',
	                      "type" => "basic",
	                      "message" =>  'Web testing shop',
	                    ),
	              );

	      $fields = json_encode ( $fields );
	      $headers = array (
	            'Authorization: key=' .$SERVER_API_KEY,
	            'Content-Type: application/json'
	      );

	      $ch = curl_init ();
	      curl_setopt ( $ch, CURLOPT_URL, $URL );
	      curl_setopt ( $ch, CURLOPT_POST, true );
	      curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
	      curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );

	      $result = curl_exec ( $ch );
	      curl_close ( $ch );
	     
	    
	    if ($result === false) {
	        $result_noti = 0;
	    } else {

	    	 //print_r($result);
	        $result_noti = 1;
	    }
    }*/

    public static function sendPushWeb($registrationId, $msgData,$settings){
    	$fields['message']['token'] = $registrationId;
        $fields['message']['notification'] = [
            'title' => $msgData['title'],
            'body' => $msgData['body'],
            // 'badge' => $msgData['badge'],
            // 'sound' => !empty($msgData['sound']) ? $msgData['sound'] : 'default',
            // 'icon' => '',//Yii::$app->params['LOGO_URL']

        ];
        $fields['message']['data'] = [
            'type' => $msgData['type'],
            'id' => !empty($msgData['id']) ? $msgData['id'] : '',
        ];

        return Helper::pushCurlCall($registrationId, $fields,$settings);

    }

	public static function sendFCMNotification($token)
	{
	    $url = 'https://fcm.googleapis.com/fcm/send';

///Last Code
	    // Your Firebase Server Key
	    $serverKey = 'AAAAiLO3YWY:APA91bHuPa6lr94gTaKZ0w7WcJ3IYNsTNSGctH024Luh1i3LJ8PphG6OunXYySps4MEDeiSE-1R2iDPiPurx-JX58iZsGug5o9NzDBMo7xp12fqBSNxO7hsPbef1t1yCd3TqDDYli1Hg';

	    // Create the payload
	    $notification = [
	        'title' => 'test',
	        'body' => 'test message',
	        'icon' => 'https://api.gridetech.com/storage/app/public/1/site/site_logo.png',
	        'sound' => 'default'
	    ];

	    $data = [
	        'to' => $token,
	        'notification' => $notification,
	        'priority' => 'high'
	    ];

	    // Encode payload as JSON
	    $jsonData = json_encode($data);

	    // Set up cURL options
	    $headers = [
	        'Authorization: key=' . $serverKey,
	        'Content-Type: application/json'
	    ];

	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

	    // Execute cURL request
	    $response = curl_exec($ch);
	    
	    // Check for errors
	    if ($response === FALSE) {
	        die('FCM Send Error: ' . curl_error($ch));
	    }

	    // Close cURL session
	    curl_close($ch);

	    return $response;
	/////last Code

	}
	/*
      Curl call
     */


    public static function pushCurlCall($registrationId, $fields,$settings) {
    	try{

	        $url = 'https://fcm.googleapis.com/v1/projects/grideapp-d362d/messages:send';   

	      	$firebaseService = new FirebaseService();

	        // Get the Access Token
	        $token = $firebaseService->getAccessToken();

	        if ($token) {
	         	
	        // if (is_array($registrationId)) {
		        //     $fields['registration_ids'] = $registrationId;
		        // } else {
		        //     $fields['to'] = $registrationId;
		        // }

		        $headers = [
		            'Authorization: Bearer ' . $token,
		            'Content-Type: application/json'
		        ];       


		        $ch = curl_init ();
				curl_setopt ( $ch, CURLOPT_URL, $url );
				curl_setopt ( $ch, CURLOPT_POST, true );
				curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode($fields) );

				$result = curl_exec ( $ch );
				//echo $result; exit;
				curl_close ( $ch );

		       \Log::info($result);
		       //print_r($result); exit;
		       return ($result) ? 1 : 0;
		    }

    	}catch(Exception $e){
    		//print_r($e->getMessage());
    		exit;
    	}
    }
	
	// public static function sendPushIOS($topic, $push_message, $title, $data, $user, $settings, $type)
	// {
		
	// 	$log = ['inside ios push'];		
	// 	\Log::info($log);
		
		
	// 	$deviceToken = $user->device_token;

	// 	if($type == 'user') {
	// 		$pem = app()->basePath('storage/app/public/'.$user->company_id.'/apns' ).'/user.pem';
	// 	} elseif($type == 'provider')  {
	// 		$pem = app()->basePath('storage/app/public/'.$user->company_id.'/apns' ).'/provider.pem';
	// 	} else {
	// 		$pem = app()->basePath('storage/app/public/'.$user->company_id.'/apns' ).'/shop.pem';
	// 	}
		
	// 	$log = ['pem file path' => $pem];
	// 	\Log::info($log);
		
	// 	$payloadArray['aps'] = [
	// 		'alert' => [
	// 			'title' => $title,
	// 			'body' => $push_message,
	// 		],
	// 		'sound' => 'default',
	// 		'badge' => 1
	// 	];

	// 	$ctx = stream_context_create();
	// 	stream_context_set_option($ctx, 'ssl', 'local_cert', $pem);        
	// 	$payload = $payloadArray;

	// 	$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
		
	// 	$body1 = $push_message;//$payload['xmpp']['body'];
	// 	// $fromuser = explode("/", $payload['xmpp']['from']);

	// 	$body['headers'] = array(
	// 		'apns-expiration' => strtotime("+7 day") * 1000,
	// 		'apns-priority' => 10,
	// 	);

	// 	$body['aps'] = array(
	// 		// 'badge' => $model->iBaseCount,
	// 		'alert' => array(
	// 			'title' => $body1,
	// 			'body' => $body1,
	// 		),
	// 		'data' => array(                                
	// 			'message' => $body1,
	// 			'vMessageTitle' => $body1,                
	// 			'sound' => 'default',
	// 		),
	// 		'sound' => 'default',
	// 			// 'bookId' => $body['bookId']
	// 	);


	// 	$payload = json_encode($body);

	// 	// Build the binary notification
	// 	$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

	// 	// Send it to the server
	// 	$result = fwrite($fp, $msg, strlen($msg));


	// 	if (!$result) {

			
	// 		$log = ['result' => 'Message not delivered'];
	// 		\Log::info($log);
	// 		// echo 'Message not delivered' . PHP_EOL;
	// 	} else {

	// 		$log = ['result' => 'Message successfully delivered'];
	// 		\Log::info($log);
	// 		// echo 'Message successfully delivered' . $body1 . PHP_EOL;
	// 	}
	// }
	
	public static function make_slug($str) {

        $string = strtolower($str);
        $chars = array(
		// Decompositions for Latin-1 Supplement
            'ª' => 'a', 'º' => 'o', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 'ß' => 's', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
            'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
            'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y', 'Ø' => 'O', 'Æ' => 'AE', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            // Decompositions for Latin Extended-A
            'Ā' => 'A', 'ā' => 'a', 'Ă' => 'A', 'ă' => 'a', 'Ą' => 'A', 'ą' => 'a', 'Ć' => 'C', 'ć' => 'c', 'Ĉ' => 'C', 'ĉ' => 'c', 'Ċ' => 'C', 'ċ' => 'c', 'Č' => 'C', 'č' => 'c', 'Ď' => 'D', 'ď' => 'd',
            'Đ' => 'D', 'đ' => 'd', 'Ē' => 'E', 'ē' => 'e', 'Ĕ' => 'E', 'ĕ' => 'e', 'Ė' => 'E', 'ė' => 'e', 'Ę' => 'E', 'ę' => 'e', 'Ě' => 'E', 'ě' => 'e', 'Ĝ' => 'G', 'ĝ' => 'g', 'Ğ' => 'G', 'ğ' => 'g',
            'Ġ' => 'G', 'ġ' => 'g', 'Ģ' => 'G', 'ģ' => 'g', 'Ĥ' => 'H', 'ĥ' => 'h', 'Ħ' => 'H', 'ħ' => 'h', 'Ĩ' => 'I', 'ĩ' => 'i', 'Ī' => 'I', 'ī' => 'i', 'Ĭ' => 'I', 'ĭ' => 'i', 'Į' => 'I', 'į' => 'i',
            'İ' => 'I', 'ı' => 'i', 'Ĳ' => 'IJ', 'ĳ' => 'ij', 'Ĵ' => 'J', 'ĵ' => 'j', 'Ķ' => 'K', 'ķ' => 'k', 'ĸ' => 'k', 'Ĺ' => 'L', 'ĺ' => 'l', 'Ļ' => 'L', 'ļ' => 'l', 'Ľ' => 'L', 'ľ' => 'l', 'Ŀ' => 'L',
            'ŀ' => 'l', 'Ł' => 'L', 'ł' => 'l', 'Ń' => 'N', 'ń' => 'n', 'Ņ' => 'N', 'ņ' => 'n', 'Ň' => 'N', 'ň' => 'n', 'ŉ' => 'n', 'Ŋ' => 'N', 'ŋ' => 'n', 'Ō' => 'O', 'ō' => 'o', 'Ŏ' => 'O', 'ŏ' => 'o',
            'Ő' => 'O', 'ő' => 'o', 'Œ' => 'OE', 'œ' => 'oe', 'Ŕ' => 'R', 'ŕ' => 'r', 'Ŗ' => 'R', 'ŗ' => 'r', 'Ř' => 'R', 'ř' => 'r', 'Ś' => 'S', 'ś' => 's', 'Ŝ' => 'S', 'ŝ' => 's', 'Ş' => 'S', 'ş' => 's',
            'Š' => 'S', 'š' => 's', 'Ţ' => 'T', 'ţ' => 't', 'Ť' => 'T', 'ť' => 't', 'Ŧ' => 'T', 'ŧ' => 't', 'Ũ' => 'U', 'ũ' => 'u', 'Ū' => 'U', 'ū' => 'u', 'Ŭ' => 'U', 'ŭ' => 'u', 'Ů' => 'U', 'ů' => 'u',
            'Ű' => 'U', 'ű' => 'u', 'Ų' => 'U', 'ų' => 'u', 'Ŵ' => 'W', 'ŵ' => 'w', 'Ŷ' => 'Y', 'ŷ' => 'y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'ź' => 'z', 'Ż' => 'Z', 'ż' => 'z', 'Ž' => 'Z', 'ž' => 'z', 'ſ' => 's',
            'Ầ' => 'A', 'ầ' => 'a', 'Ằ' => 'A', 'ằ' => 'a', 'Ề' => 'E', 'ề' => 'e', 'Ồ' => 'O', 'ồ' => 'o',
            'Ờ' => 'O', 'ờ' => 'o', 'Ừ' => 'U', 'ừ' => 'u',
            'Ỳ' => 'Y', 'ỳ' => 'y',
            // hook
            'Ả' => 'A', 'ả' => 'a', 'Ẩ' => 'A', 'ẩ' => 'a', 'Ẳ' => 'A', 'ẳ' => 'a', 'Ẻ' => 'E', 'ẻ' => 'e', 'Ể' => 'E', 'ể' => 'e', 'Ỉ' => 'I', 'ỉ' => 'i', 'Ỏ' => 'O', 'ỏ' => 'o', 'Ổ' => 'O', 'ổ' => 'o',
            'Ở' => 'O', 'ở' => 'o', 'Ủ' => 'U', 'ủ' => 'u', 'Ử' => 'U', 'ử' => 'u', 'Ỷ' => 'Y', 'ỷ' => 'y',
            // tilde
            'Ẫ' => 'A', 'ẫ' => 'a', 'Ẵ' => 'A', 'ẵ' => 'a', 'Ẽ' => 'E', 'ẽ' => 'e', 'Ễ' => 'E', 'ễ' => 'e', 'Ỗ' => 'O', 'ỗ' => 'o', 'Ỡ' => 'O', 'ỡ' => 'o', 'Ữ' => 'U', 'ữ' => 'u', 'Ỹ' => 'Y', 'ỹ' => 'y',
            // acute accent
            'Ấ' => 'A', 'ấ' => 'a', 'Ắ' => 'A', 'ắ' => 'a', 'Ế' => 'E', 'ế' => 'e', 'Ố' => 'O', 'ố' => 'o', 'Ớ' => 'O', 'ớ' => 'o', 'Ứ' => 'U', 'ứ' => 'u',
            // dot below
            'Ạ' => 'A', 'ạ' => 'a', 'Ậ' => 'A', 'ậ' => 'a', 'Ặ' => 'A', 'ặ' => 'a', 'Ẹ' => 'E', 'ẹ' => 'e', 'Ệ' => 'E', 'ệ' => 'e', 'Ị' => 'I', 'ị' => 'i', 'Ọ' => 'O', 'ọ' => 'o', 'Ộ' => 'O', 'ộ' => 'o',
            'Ợ' => 'O', 'ợ' => 'o', 'Ụ' => 'U', 'ụ' => 'u', 'Ự' => 'U', 'ự' => 'u', 'Ỵ' => 'Y', 'ỵ' => 'y',
            // Vowels with diacritic (Chinese, Hanyu Pinyin)
            'ɑ' => 'a',
            // macron
            'Ǖ' => 'U', 'ǖ' => 'u',
            // acute accent
            'Ǘ' => 'U', 'ǘ' => 'u',
            // caron
            'Ǎ' => 'A', 'ǎ' => 'a', 'Ǐ' => 'I', 'ǐ' => 'i', 'Ǒ' => 'O', 'ǒ' => 'o', 'Ǔ' => 'U', 'ǔ' => 'u', 'Ǚ' => 'U', 'ǚ' => 'u',
            // grave accent
            'Ǜ' => 'U', 'ǜ' => 'u',
            // Decompositions for Ltin Extended-B
            'Ș' => 'S', 'ș' => 's', 'Ț' => 'T', 'ț' => 't',
            // Euro Sign
            '€' => 'E',
            // GBP (Pound) Sign
            '£' => '',
            // Vowels with diacritic (Vietnamese)
// unmarked
            'Ơ' => 'O', 'ơ' => 'o', 'Ư' => 'U', 'ư' => 'u',
                // grave accent
        );

        $string = html_entity_decode($string);
        $string = str_replace(array_keys($chars), $chars, $string);
        $string = preg_replace('#[^\w\säüöß]#', null, $string);
        $string = preg_replace('#[\s]{2,}#', ' ', $string);
        $string = preg_replace('~[^\pL\d]+~u', '-', $string);
        $string = preg_replace('~[^-\w]+~', '', $string);
        $string = trim($string, '-');
        $string = preg_replace('~-+~', '-', $string);
        $string = str_replace(array(' '), array('-'), $string);
        return $string;
    }
}