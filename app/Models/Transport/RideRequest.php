<?php

namespace App\Models\Transport;

use App\Models\BaseModel;
use App\Helpers\Helper;

class RideRequest extends BaseModel
{
	protected $connection = 'transport';

	protected $fillable = [
		'provider_id',
		'user_id',
		'service_type_id',
		'promocode_id',
		'rental_hours',
		'status',
		'cancelled_by',
		'is_track',
		'otp',
		'travel_time',
		'distance',
		's_latitude',
		'd_latitude',
		's_longitude',
		'd_longitude',
		'track_distance',
		'track_latitude',
		'track_longitude',
		'paid',
		's_address',
		'd_address',
		'assigned_at',
		'schedule_at',
		'is_scheduled',
		'started_at',
		'finished_at',
		'use_wallet',
		'user_rated',
		'provider_rated',
		'surge',
		'company_id'      
	];

	protected $hidden = [
     	'company_id', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'updated_at', 'deleted_at'
     ];

	protected $dates = [
		'created_at',
		'updated_at',
		'deleted_at',
		'assigned_at',
		'schedule_at',
		'started_at',
		'finished_at',
        'comapny_id'
	];

    protected $appends = ['created_time','assigned_time', 'schedule_time', 'started_time', 'finished_time'];

    public function scopeuserHistorySearch($query, $searchText='') {
        if ($searchText != '') {
            $result =  $query
            ->where('booking_id', 'like', "%" . $searchText . "%")
            ->orWhere('status', 'like', "%" . $searchText . "%")
            ->orWhere('payment_mode', 'like', "%" . $searchText . "%");
        }
        return $result;
    }

    public function scopehistroySearch($query, $searchText='') {
        if ($searchText != '') {
            $result =  $query
            ->where('booking_id', 'like', "%" . $searchText . "%")
            ->orWhere('status', 'like', "%" . $searchText . "%")
            ->orwhereHas('ride', function ($q) use ($searchText){
            $q->where('vehicle_name', 'like', "%" . $searchText . "%");
                })
            ->orWhere('payment_mode', 'like', "%" . $searchText . "%");
        }
        return $result;
    }

    public function scopeProviderhistroySearch($query, $searchText='') {
        if ($searchText != '') {
            $result =  $query
            ->where('booking_id', 'like', "%" . $searchText . "%")
            ->orWhere('status', 'like', "%" . $searchText . "%")
            ->orWhere('s_address', 'like', "%" . $searchText . "%")
            ->orWhere('d_address', 'like', "%" . $searchText . "%")
            ->orwhereHas('payment', function ($q) use ($searchText){
            $q->where('total', 'like', "%" . $searchText . "%");
                });
            
        }
        return $result;
    }

     public function scopeHistoryUserTrips($query, $user_id,$showType='')
    {
        if($showType !=''){
          if($showType == 'past'){
                $history_status = array('CANCELLED','COMPLETED');
          }else if($showType=='upcoming'){
                $history_status = array('SCHEDULED');
          }else{
                $history_status = array('SEARCHING','ACCEPTED','STARTED','ARRIVED','PICKEDUP','DROPPED');
          }
        return $query->where('ride_requests.user_id', $user_id)
                    ->whereIn('ride_requests.status',$history_status)
                    ->orderBy('ride_requests.created_at','desc');
        }else{
            
        }
    }

     public function scopeHistoryProvider($query, $provider_id,$historyStatus)
    {
        return $query->where('provider_id', $provider_id)
                    ->whereIn('status',$historyStatus)
                    ->orderBy('created_at','desc');
    }



    public function getCreatedTimeAttribute() {
        return (isset($this->attributes['created_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['created_at'], 'UTC'))->setTimezone($this->attributes['timezone'])->format(Helper::dateFormat(1)) : '' ;
    }
    public function getAssignedTimeAttribute() {
        return (isset($this->attributes['assigned_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['assigned_at'], 'UTC'))->setTimezone($this->attributes['timezone'])->format(Helper::dateFormat(1)) : '';
    }

    public function getScheduleTimeAttribute() {
        return (isset($this->attributes['schedule_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['schedule_at'], 'UTC'))->setTimezone($this->attributes['timezone'])->format(Helper::dateFormat(1)) : '' ;
        
    }

    public function getStartedTimeAttribute() {
        return (isset($this->attributes['started_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['started_at'], 'UTC'))->setTimezone($this->attributes['timezone'])->format(Helper::dateFormat(1)) : '' ;
        
    }

    public function getFinishedTimeAttribute() {
        return (isset($this->attributes['finished_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['finished_at'], 'UTC'))->setTimezone($this->attributes['timezone'])->format(Helper::dateFormat(1)) : '' ;
        
    }
    
    /**
     * UserRequestPayment Model Linked
     */
    public function payment()
    {
        return $this->hasOne('App\Models\Transport\RideRequestPayment', 'ride_request_id');
    }

    /**
     * UserRequestRating Model Linked
     */
    public function rating()
    {
        return $this->hasOne('App\Models\Common\Rating', 'request_id');
    }

    /**
     * UserRequestRating Model Linked
     */
    public function filter()
    {
        return $this->hasMany('App\Models\Transport\RideFilter', 'ride_request_id');
    }

    /**
     * The user who created the request.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Common\User');
    }

    /**
     * The provider assigned to the request.
     */
    public function provider()
    {
        return $this->belongsTo('App\Models\Common\Provider', 'provider_id');
    }

    public function provider_vehicle()
    {
        return $this->hasOne('App\Models\Common\ProviderVehicle', 'id', 'provider_vehicle_id');
    }

    public function ride()
    {
       return $this->belongsTo('App\Models\Transport\RideDeliveryVehicle', 'ride_delivery_id');
    }

    public function ride_type()
    {
       return $this->belongsTo('App\Models\Transport\RideType', 'ride_type_id');
    }

    public function chat()
    {
       return $this->hasOne('App\Models\Common\Chat', 'request_id');
    }

    public function service_type()
    {
        return $this->belongsTo('App\Models\Common\ProviderService', 'provider_id', 'provider_id');
    }

    public function scopePendingRequest($query, $user_id)
    {
        return $query->where('user_id', $user_id)
                ->whereNotIn('status' , ['CANCELLED', 'SCHEDULED'])
                ->where(function($q){
                    $q->where('paid', '<>', 1)
                        ->orWhereNull('paid');
                    }
                );
    }

	public function scopeRideRequestStatusCheck($query, $user_id, $check_status, $admin_service,$type)
	{
		return $query->where('ride_requests.user_id', $user_id)
					->where('ride_requests.user_rated',0)
					->whereNotIn('ride_requests.status', $check_status)
					->select('ride_requests.*')
					->with(['user','provider','service_type' => function($query) use($admin_service,$type) {
                         $query->where('admin_service', $admin_service);
                         if($type!=0)
                         $query->where('ride_delivery_id',$type);
                    },'ride','service_type.vehicle','payment','rating','chat']);
	}

    

	public function scopeRideRequestAssignProvider($query, $user_id, $check_status)
    {
        return $query->where('ride_requests.user_id', $user_id)
                    ->whereNull('ride_requests.provider_id')
                    ->whereIn('ride_requests.status', $check_status)
                    ->select('ride_requests.*');
    }
    public function scopeUserTrips($query, $user_id)
    {
        return $query->where('ride_requests.user_id', $user_id)
                    ->where('ride_requests.status','!=','SCHEDULED')
                    ->orderBy('ride_requests.created_at','desc')
                    ->select('ride_requests.*');
    }

    public function scopeUserUpcomingTrips($query, $user_id)
    {
        return $query->where('ride_requests.user_id', $user_id)
                    ->where('ride_requests.status', 'SCHEDULED')
                    ->orderBy('ride_requests.created_at','desc')
                    ->select('ride_requests.*');
    }
    public function dispute() 
    {
        return $this->belongsTo('App\Models\Transport\RideRequestDispute','id','ride_request_id');
    }
    public function lostItem()
    {
        return $this->belongsTo('App\Models\Transport\RideLostItem','id','ride_request_id');
    }


}
