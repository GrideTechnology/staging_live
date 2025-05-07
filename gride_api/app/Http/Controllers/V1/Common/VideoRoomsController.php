<?php

namespace App\Http\Controllers\V1\Common;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use Twilio\Rest\Client;
use Auth;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;
use Twilio\TwiML\VoiceResponse;

use Twilio\Jwt\Grants\VoiceGrant;
use App\Services\SendPushNotification;
use App\User;
use App\Models\Common\Setting;
use App\ChatFilter;
use App\Helpers\Helper;

class VideoRoomsController extends Controller
{
	protected $sid;
	protected $token;
	protected $key;
	protected $secret;
	protected $phone_no;
	protected $app_sid;

	public function __construct()
	{

		$settings = json_decode(json_encode(Setting::first()->settings_data));
		$siteConfig = $settings->site; 

        $this->sid = $siteConfig->sms_account_sid;
        $this->token = $siteConfig->sms_auth_token;
        $this->key = $siteConfig->twilio_key;
        $this->secret = $siteConfig->twilio_secret;
        $this->phone_no = $siteConfig->sms_from_number;
        $this->app_sid = 'AP200f9ead73e9de96641cd9b4176728b4'; //$siteConfig->app_sid;
	}

	public function index(Request $request)
	{
		// A unique identifier for this user
		$identity = Auth::user()->id;

		\Log::debug("joined with identity: $identity");
		$token = new AccessToken($this->sid, $this->key, $this->secret, 3600, $identity);

		$chatFilter = ChatFilter::where('caller_id', Auth::id())->orWhere('receiver_id', Auth::id())->first();

		$user=Auth::user(); 

		if($chatFilter != null) {
			$videoGrant = new VideoGrant();
			$videoGrant->setRoom($chatFilter->room_id);
 
			$token->addGrant($videoGrant);

			if($request->ajax()){
				return response()->json(['accessToken' => $token->toJWT(), 'roomName' => $chatFilter->room_id]);
			}else{
				return view('video', [ 'accessToken' => $token->toJWT(), 'roomName' => $chatFilter->room_id, 'user' => $user ]);
			}

		} else {
			//dd('test');
			/* if($request->ajax()){
				return response()->json([]);
			}else{
				return view('room');
			} */

		}

		//dd($chatFilter);
 
		$videoGrant = new VideoGrant();
		$videoGrant->setRoom($roomName);
 
		$token->addGrant($videoGrant);
 
		if($request->ajax()){
			  return response()->json(['accessToken' => $token->toJWT(), 'roomName' => $roomName]);
		}else{
			 return view('video', [ 'accessToken' => $token->toJWT(), 'roomName' => $roomName ]);
		}

		return view('video', ['rooms' => $rooms, 'user' => $user]);

	   $rooms = [];
	   try {
	       $client = new Client($this->sid, $this->token);
	       $allRooms = $client->video->rooms->read([]);

	        $rooms = array_map(function($room) {
	           return $room->uniqueName;
			}, $allRooms);
			
			$user=Auth::user(); 

	   } catch (Exception $e) {
	       echo "Error: " . $e->getMessage();
	   }

	   if($request->ajax()){
	   	  return response()->json(['rooms' => $rooms]);

	   }else{

	   	 return view('video', ['rooms' => $rooms, 'user' => $user]);

	   }

	   
	}

	public function createRoom(Request $request)
	{
	   $client = new Client($this->sid, $this->token);

	   $exists = $client->video->rooms->read([ 'uniqueName' => $request->roomName]);

	   if (empty($exists)) {
	       $client->video->rooms->create([
	           'uniqueName' => $request->roomName,
	           'type' => 'group',
	           'recordParticipantsOnConnect' => false
	       ]);

	       \Log::debug("created new room: ".$request->roomName);
	   }

	    return response()->json(['roomName' => $request->roomName]);

	   
	}

	public function joinRoom(Request $request,$roomName)
	{
	   // A unique identifier for this user
	   $identity = "user ".Auth::user()->first_name;



	   \Log::debug("joined with identity: $identity");
	   $token = new AccessToken($this->sid, $this->key, $this->secret, 3600, $identity);

	   $videoGrant = new VideoGrant();
	   $videoGrant->setRoom($roomName);

	   $token->addGrant($videoGrant);

	   if($request->ajax()){

	   	  return response()->json(['accessToken' => $token->toJWT(), 'roomName' => $roomName]);

	   }else{
	   	 return view('room', [ 'accessToken' => $token->toJWT(), 'roomName' => $roomName ]);
	   }

	  
	}

	public function accesstoken(Request $request)
	{
	   // A unique identifier for this user
	   $identity = "user_".Auth::guard('user')->user()->first_name;

	   $user_name = Auth::guard('user')->user()->first_name;

	   $roomName = $request->room_id;

    	\Log::debug("joined with identity: $identity");
	   $token = new AccessToken($this->sid, $this->key, $this->secret, 3600, $identity);
		\Log::info('accessToken'.$token);
	   $videoGrant = new VideoGrant();
	   $videoGrant->setRoom($roomName);

	   $token->addGrant($videoGrant);

	    if($request->video==1)
	   $message = "video_call";
		else
	   $message = "audio_call";


	   $accesstoken = $token->toJWT();

	   if($request->push==1)
	   (new SendPushNotification)->sendPushToProviderVideo($request->id,$message,$user_name,$roomName,$request->video,$accesstoken);

	 return Helper::getResponse(['data'=> ['accessToken' => $token->toJWT()]]); 

	   /*if($request->ajax()){

	   	  return response()->json(['accessToken' => $token->toJWT()]);

	   }else{
	   	 return view('room', [ 'accessToken' => $token->toJWT()]);
	   }
*/
	  
	}

	public function voiceaccesstoken(Request $request)
	{
	   // A unique identifier for this user
	   $identity = "user_".mt_rand(1111,9999);

	  // $user_name = Auth::user()->first_name;

	   $twilioAccountSid = '';
		$twilioApiKey = '';	
		$twilioApiSecret = '';
		$twilioPushCred = '';
							

	   $outgoingApplicationSid='';

	    \Log::debug("joined with identity: $identity");
		$token = new AccessToken('', '', '', 3600, $identity);
		//print_r($token); exit;
	    $voiceGrant = new VoiceGrant();
		$voiceGrant->setOutgoingApplicationSid($outgoingApplicationSid);
        $voiceGrant->setPushCredentialSid($twilioPushCred);
		// Optional: add to allow incoming calls
		$voiceGrant->setIncomingAllow(true);

		// Add grant to token
		$token->addGrant($voiceGrant);

        //return response()->json(['accessToken' => $token->toJWT()]);
        return Helper::getResponse(['data'=> ['accessToken' => $token->toJWT()]]);

 		  
	}

	public function CreateCall(){
		
		$twilioAccountSid = '';
		$auth_token = '';
		$twilio_number = '+18332246135';
		$identity = "user_".mt_rand(1111,9999);
		$client = new Client($twilioAccountSid, $auth_token);
		$service = $client->proxy->v1->services
                             ->create($identity);

		print($service->sid);
		exit;
		print_r($call);
	}

	 public function dial_number(Request $request)
    {
		  

           \Log::info($request->all());
         try{
            while(ob_get_level()) ob_end_clean();
           $twilio_number = $this->phone_no;


            $to_number = $request->phone;

            $response = new VoiceResponse();
            $dial = $response->dial('', ['callerId' => $twilio_number]);
            $dial->number($to_number);

         //  $response = simplexml_load_string($response);

            return $response;



          }catch(Exception $e){
           \Log::info($e);
          }


	}

	public function Provideraccesstoken(Request $request)
	{
	   // A unique identifier for this user
	   $identity = "user_".Auth::guard('provider')->user()->first_name;

	   $user_name = Auth::guard('provider')->user()->first_name;

	   $roomName = $request->room_id;

       \Log::debug("joined with identity: $identity");
	   $token = new AccessToken($this->sid, $this->key, $this->secret, 3600, $identity);
		\Log::info('accessToken'.$token);
	   $videoGrant = new VideoGrant();
	   $videoGrant->setRoom($roomName);

	   $token->addGrant($videoGrant);

	    if($request->video==1)
	   $message = "video_call";
		else
	   $message = "audio_call";


	   $accesstoken = $token->toJWT();
	   if($request->push==1)
	   (new SendPushNotification)->sendPushToUserVideo($request->id,$message,$user_name,$roomName,$request->video,$accesstoken); 

		return Helper::getResponse(['data'=> ['accessToken' => $token->toJWT()]]);

	   /*if($request->ajax()){

	   	  return response()->json(['accessToken' => $token->toJWT()]);

	   }else{
	   	 return view('room', [ 'accessToken' => $token->toJWT()]);
	   }*/

	  
	}

	public function declinetoken(Request $request)
    {
       // A unique identifier for this user
       //$user = User::findOrFail($request->id);
       $identity = "user_".Auth::guard('user')->user()->first_name;
	   $user_name = Auth::guard('user')->user()->first_name;
       // $roomName = $request->room_id;
       $roomName = '';
   // \Log::debug("joined with identity: $identity");
       // $token = new AccessToken($this->sid, $this->key, $this->secret, 3600, $identity);
       // $videoGrant = new VideoGrant();
       // $videoGrant->setRoom($roomName);
       // $token->addGrant($videoGrant);
       $message = "Call Declined";
       $push = (new SendPushNotification)->sendPushToProviderVideo($request->id,$message,$user_name,$roomName,$request->video,'');

       return Helper::getResponse(['data'=> ['message' => $message]]);
       /*if($request->ajax()){
             return response()->json(['message' => $message]);
       }else{
            return view('room', ['flash_success' => $message]);
       }*/
      
    } 

    public function provider_declinetoken(Request $request)
    {
    	$identity = "user_".Auth::guard('provider')->user()->first_name;
	   	$user_name = Auth::guard('provider')->user()->first_name;
		$roomName = '';
		$message = "Call Declined";
		$push = (new SendPushNotification)->sendPushToUserVideo($request->id,$message,$user_name,$roomName,$request->video,'');

		return Helper::getResponse(['data'=> ['message' => $message]]);
       /*if($request->ajax()){
             return response()->json(['message' => $message]);
       }else{
            return view('room', ['flash_success' => $message]);
       }*/
    }


}
