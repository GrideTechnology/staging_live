<?php

namespace App\Http\Controllers\V1\Transport\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transport\RideChat;
use App\Models\Common\User;
use App\Models\Common\Owner;
use App\Helpers\Helper;
use App\Traits\Actions;
use Carbon\Carbon;
use Auth;
use DB;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
	use Actions;



    public function ownerchatinbox(){

    	$user = Auth::guard('owner')->user();
    	
    	$data= RideChat::select('ride_chats.user_id', 'ride_common.users.picture', 'ride_common.users.first_name', 'ride_common.users.last_name')->distinct()->join('ride_common.users', 'ride_common.users.id', '=', 'ride_chats.user_id')->where('ride_chats.owner_id', $this->user->id)->get();
        
       	return Helper::getResponse(['data' => $data]);
			
    }

    public function usermessages(Request $request)
    {
    	$id = $request->id;
    	$user = Auth::guard('owner')->user();
    	
    	$data['user']= RideChat::select('ride_chats.user_id', 'ride_chats.message', 'ride_chats.from', 'ride_common.users.picture', 'ride_common.users.first_name', 'ride_common.users.last_name')->join('ride_common.users', 'ride_common.users.id', '=', 'ride_chats.user_id')->where('ride_chats.owner_id', '=', $this->user->id)->where('ride_chats.user_id', '=', $id)->get();

    	RideChat:: where('user_id', '=', $id)->where('owner_id', '=', $this->user->id)->where('from', '=', 'user')->update(array('status' => 1));

        $data['owner'] = $user;

       	return Helper::getResponse(['data' => $data]);
    }

    public function userchatinbox(){

    	$user = Auth::guard('user')->user();
    	
    	$data['chats']= RideChat::select('ride_chats.owner_id', 'ride_common.owners.picture', 'ride_common.owners.first_name', 'ride_common.owners.last_name')->distinct()->join('ride_common.owners', 'ride_common.owners.id', '=', 'ride_chats.owner_id')->where('ride_chats.user_id', $this->user->id)->get();

        
       	return Helper::getResponse(['data' => $data]);
			
    }

    public function ownermessages(Request $request)
    {
    	$id = $request->id;
    	$user = Auth::guard('user')->user();
    	$data['owner']=array();
    	$data['owner']= RideChat::select('ride_chats.owner_id', 'ride_chats.message', 'ride_chats.from', 'ride_common.owners.picture', 'ride_common.owners.first_name', 'ride_common.owners.last_name')->join('ride_common.owners', 'ride_common.owners.id', '=', 'ride_chats.owner_id')->where('ride_chats.user_id', '=', $this->user->id)->where('ride_chats.owner_id', '=', $id)->get();

    	RideChat:: where('user_id', '=', $this->user->id)->where('owner_id', '=', $id)->where('from', '=', 'owner')->update(array('status' => 1));
    	
    	if(count($data['owner'])==0){
    		$data['owner'] = Owner::select('first_name', 'last_name', 'picture')->where('id', '=', $id)->get();
    		$data['owner'][0]['message']="notfound";
    	}
        $data['user'] = $user;

       	return Helper::getResponse(['data' => $data]);
    }

    public function sendOwnerMessage(Request $request)
    {
    	$user = Auth::guard('owner')->user();
    	try{
	    	$message = new RideChat;
	    	$message->message = $request->message;
	    	$message->user_id = $request->user;
	    	$message->owner_id = $this->user->id;
	    	$message->from = 'owner';
	    	$message->save();

      		return Helper::getResponse(['status' => 200, 'message' => 'Message sent successfully']);
	    }
	    catch (\Throwable $e) {
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
      	}
    }

     public function sendUserMessage(Request $request)
    {
    	$user = Auth::guard('user')->user();
    	try{
	    	$message = new RideChat;
	    	$message->message = $request->message;
	    	$message->owner_id = $request->owner;
	    	$message->user_id = $this->user->id;
	    	$message->from = 'user';
	    	$message->save();

      		return Helper::getResponse(['status' => 200, 'message' => 'Message sent successfully']);
	    }
	    catch (\Throwable $e) {
            return Helper::getResponse(['status' => 500, 'error' => $e->getMessage()]);
      	}
    }

    public function getnotification(){
    	$user = Auth::guard('user')->user();

    	$data['newmessages'] = RideChat::where('user_id', '=', $this->user->id)->where('from', '=', 'owner')->where('status', '=', '0')->count();
        
       	return Helper::getResponse(['data' => $data]);
    }

    public function getOwnernotification(){
    	$user = Auth::guard('owner')->user();

    	$data['newmessages'] = RideChat::where('owner_id', '=', $this->user->id)->where('from', '=', 'user')->where('status', '=', '0')->count();
        
       	return Helper::getResponse(['data' => $data]);
    } 


    
}