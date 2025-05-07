<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\V1\Common\Admin\Resource\AdminController;
use App\Models\Common\RequestFilter;
use App\Services\SendPushNotification;
use App\Models\Common\AdminService;
use App\Models\Transport\RideRequest;
use App\Models\Common\UserRequest;
use App\Models\Common\Provider;
use App\Models\Common\Setting;
use Carbon\Carbon;
use DB;

class ScheduleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob:rides';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updating the Scheduled Rides Timing';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
        $userRequest = UserRequest::where('status','SCHEDULED')
                        ->where('admin_service', 'TRANSPORT')
                        ->whereNull('provider_id')
                        ->where('schedule_at','<=',Carbon::now()->addMinutes(20))
                        ->get();

        $hour = Carbon::now()->subHour();
        $futurehours = Carbon::now()->addMinutes(20);
        $date = Carbon::now();   

        \Log::info("Schedule transport Request Started.".$date."==".$hour."==".$futurehours);

        if(!empty($userRequest)){
            
            foreach($userRequest as $newRequest){

                $rideRequest = RideRequest::find($newRequest->request_id);

                if($rideRequest != null) {

                    $setting = Setting::where('company_id', $rideRequest->company_id)->first();

                    $settings = json_decode(json_encode($setting->settings_data));

                    $siteConfig = $settings->site;

                    $transportConfig = $settings->transport; 

                    $distance = isset($transportConfig->provider_search_radius) ? $transportConfig->provider_search_radius : 10;
                    $latitude = $rideRequest->s_latitude;
                    $longitude = $rideRequest->s_longitude;
                    $ride_delivery_id = $rideRequest->ride_delivery_id;


                    $Provider = Provider::find($rideRequest->provider_id);

                    // if(!empty($Providers->toArray())) {

                    //     if($transportConfig->manual_request == 0) {
                    //         foreach ($Providers as $Provider) {

                                // if($transportConfig->broadcast_request == 1){
                                    (new SendPushNotification)->IncomingRequest($Provider->id, 'TRANSPORT'); 
                                // }

                                $Filter = new RequestFilter;
                                // Send push notifications to the first provider
                                // incoming request push to provider
                                $Filter->admin_service = 'TRANSPORT';
                                $Filter->request_id = $newRequest->id;
                                $Filter->provider_id = $Provider->id; 
                                $Filter->company_id = $newRequest->company_id;
                                $Filter->save();
                        //     }
                        // }

                        $rideRequest->status = "SEARCHING";
                        $rideRequest->assigned_at = Carbon::now();
                        $rideRequest->schedule_at = null;
                        $rideRequest->save();

                        $rideData = RideRequest::with('ride', 'ride_type')->where('id', $rideRequest->id)->first();

                        $newRequest->status = $rideRequest->status;
                        $newRequest->request_data = json_encode($rideData);
                        $newRequest->save();

                        $user_request = UserRequest::find($newRequest->id);
                        $user_request->provider_id = $rideRequest->provider_id;
                        $user_request->save();

                        //Send message to socket
                        $requestData = ['type' => 'TRANSPORT', 'room' => 'room_'.$rideRequest->company_id, 'id' => $rideRequest->id, 'city' => ($setting->demo_mode == 0) ? $rideRequest->city_id : 0, 'user' => $rideRequest->user_id ];
                        app('redis')->publish('newRequest', json_encode( $requestData ));

                        $requestData = ['type' => 'TRANSPORT', 'room' => 'room_'.$rideRequest->company_id, 'id' => $rideRequest->id, 'city' => ($setting->demo_mode == 0) ? $rideRequest->city_id : 0, 'user' => $rideRequest->user_id ];
                        app('redis')->publish('checkTransportRequest', json_encode( $requestData ));
                    // }
                    // else{
                            
                    //     $rideRequest->status = "CANCELLED";
                    //     $rideRequest->assigned_at = Carbon::now();
                    //     $rideRequest->schedule_at = null;
                    //     $rideRequest->cancel_reason = 'Scheduled provider not found';
                    //     $rideRequest->save();

                    //     $user_request = UserRequest::where('admin_service', 'TRANSPORT')->where('request_id',$rideRequest->id)->first();
                    //     $user_request->delete();

                    // }

                }
                
                 //scehule start request push to user
                (new SendPushNotification)->user_schedule($rideRequest->user_id, 'transport');
                 //scehule start request push to provider
                (new SendPushNotification)->provider_schedule($rideRequest->provider_id, 'transport');
                \Log::info("Notification sent for scheduled ride.".$date."==".$hour."==".$futurehours);
            }
        }

        $CustomPush = DB::connection('common')->table('custom_pushes')
                      ->where('schedule_at','<=',Carbon::now()->addMinutes(5))
                      ->get();

        if(!empty($CustomPush)){
            foreach($CustomPush as $Push){
                DB::table('custom_pushes')
                        ->where('id',$Push->id)
                        ->update(['schedule_at' => null ]);

                // sending push
                (new AdminController)->SendCustomPush($Push->id);
            }
        }

    }
}
