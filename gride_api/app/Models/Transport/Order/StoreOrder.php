<?php

namespace App\Models\Order;

use App\Models\BaseModel;
use App\Helpers\Helper;

class StoreOrder extends BaseModel
{
   
    protected $connection = 'order';
    protected $appends = ['created_time','assigned_time','delivery','pickup'];

    protected $fillable = [
        'id', 'admin_service', 'store_order_invoice_id', 'user_id', 'user_address_id', 'promocode_id', 'store_id', 'provider_id', 'provider_vehicle_id', 'company_id', 'city_id', 'country_id', 'note', 'description', 'route_key', 'delivery_date', 'schedule_datetime', 'pickup_address', 'delivery_address', 'distance', 'order_type', 'order_otp', 'order_ready_time', 'order_ready_status', 'paid', 'user_rated', 'provider_rated', 'cancelled_by', 'cancel_reason', 'currency', 'status', 'schedule_status', 'assigned_at', 'timezone', 'request_type','leave_at_door'      
    ];

    protected $hidden = [
        'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'updated_at', 'deleted_at'
    ];
     
    public function invoice()
   	{
       return $this->hasOne('App\Models\Order\StoreOrderInvoice', 'store_order_id');

   	}
   	public function store()
   	{
       return $this->hasOne('App\Models\Order\Store','id','store_id')->select('store_name','id','latitude','longitude','store_type_id','estimated_delivery_time');
   	}
   	public function deliveryaddress()
   	{
       return $this->hasOne('App\Models\Common\UserAddress','id','user_address_id')->select('map_address','id','latitude','longitude');
   	}

    public function storesDetails()
   	{
       return $this->hasOne('App\Models\Order\Store', 'id','store_id');
    }
    public function orderInvoice()
   	{
       return $this->hasOne('App\Models\Order\StoreOrderInvoice', 'store_order_id','id');
    }
       /**
     * The user who created the request.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Common\User');
    }

    public function provider()
    {
        return $this->belongsTo('App\Models\Common\Provider');
    }

    public function service()
    {
        return $this->belongsTo('App\Models\Common\AdminService', 'admin_service', 'admin_service');
    }

    public function storecuisine()
    {
        return $this->hasmany('App\Models\Order\StoreCuisines', 'store_id', 'store_id');
    }
    
    public function rating()
    {
        return $this->hasOne('App\Models\Common\Rating', 'request_id');
    }
    
    public function chat()
    {
       return $this->hasOne('App\Models\Common\Chat', 'request_id');
    }
    
    public function getDeliveryAttribute() {
        return json_decode($this->attributes['delivery_address']);        
    }

    public function getPickupAttribute() {
        return json_decode($this->attributes['pickup_address']);        
    }
    public function scopeHistoryUserTrips($query, $user_id,$showType='')
    {

        if($showType !=''){
        if($showType == 'past'){
            $history_status = array('CANCELLED','COMPLETED');
        } else if($showType == 'upcoming'){
            $history_status = array('SCHEDULED');
        }
        else{
            $history_status = array('SEARCHING','ACCEPTED','STARTED','ARRIVED','PICKEDUP','DROPPED');
        }
       
        return $query->where('user_id', $user_id)
                    ->whereIn('status',$history_status)
                    ->orderBy('created_at','desc');
        }
    }

        public function scopeHistoryProvider($query, $provider_id,$historyStatus)
    {
        return $query->where('provider_id', $provider_id)
                    ->whereIn('status',$historyStatus)
                    ->orderBy('created_at','desc');
    }

    public function scopePendingRequest($query, $user_id, $store_id)
    {
        return $query->where('user_id', $user_id)
                ->where('store_id', $store_id)
                ->whereNotIn('status' , ['CANCELLED', 'SCHEDULED','PROVIDEREJECTED'])
                ->where(function($q){
                    $q->where('paid', '<>', 1)
                        ->orWhereNull('paid');
                    }
                );
    }


    public function scopeOrderRequestStatusCheck($query, $user_id, $check_status, $admin_service)
	{
		return $query->where('store_orders.user_id', $user_id)
					->where('store_orders.user_rated',0)
                    ->whereNotIn('store_orders.status', $check_status)
                    ->where('admin_service', $admin_service)
					->select('store_orders.*')
					->with(['user','provider','store.storetype','deliveryaddress','invoice','rating']);
	}
    public function scopeuserHistorySearch($query, $searchText='') {
        return $query->
            whereHas('invoice',function($q) use ($searchText){
            $q->where('payment_mode', 'like', "%" . $searchText . "%");
            })
            ->Orwhere('store_order_invoice_id', 'like', "%" . $searchText . "%")
            ->orWhere('status', 'like', "%" . $searchText . "%") ;
            
    }
    public function scopeProviderhistorySearch($query, $searchText='') {

        return $query
            ->where('store_order_invoice_id', 'like', "%" . $searchText . "%")
            ->OrwhereHas('orderInvoice',function($q) use ($searchText){
            $q->where('cash', 'like', "%" . $searchText . "%");
            })
            ->OrwhereHas('store',function($q) use ($searchText){
            $q->where('store_location', 'like', "%" . $searchText . "%");
            $q->where('store_name', 'like', "%" . $searchText . "%");
            });
           
            
    }
    public function scopeOrderUserUpcomingTrips($query, $user_id)
    {
        return $query->where('store_orders.user_id', $user_id)
                    ->where('store_orders.status', 'SCHEDULED')
                    ->orderBy('store_orders.created_at','desc');
    }

     public function getAssignedTimeAttribute() {
      
        return (isset($this->attributes['assigned_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['assigned_at'], 'UTC'))->setTimezone($this->attributes['timezone'])->format(Helper::dateFormat(1)) : '' ;
        
    }

    public function getCreatedTimeAttribute() {
        
        return (isset($this->attributes['created_at'])) ? (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['created_at'], 'UTC'))->setTimezone($this->attributes['timezone'])->format(Helper::dateFormat(1)) : '' ;
        
    }

    public function storeOrderDispute()
    {
       return $this->belongsTo('App\Models\Order\StoreOrderDispute', 'id','store_order_id');//->where('status','open');
    }

     public function dispute()
    {
       return $this->belongsTo('App\Models\Order\StoreOrderDispute', 'id','store_order_id');//->where('status','open');
    }

    public function scopeSearch($query, $searchText='') {
        return $query
          ->where('store_order_invoice_id', 'like', "%" . $searchText . "%");
          //->orwhere('store_location', 'like', "%" . $searchText . "%");
          //->orwhere('contact_number', 'like', "%" .$this->cusencrypt($searchText,env('DB_SECRET')). "%")
          //->orwhere('email', 'like', "%" .$this->cusencrypt($searchText,env('DB_SECRET')). "%");
          
            
    }
}
