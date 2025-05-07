<?php

namespace App\Models\Order;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use App\Models\BaseModel;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use NotificationChannels\WebPush\HasPushSubscriptions;
use App\Traits\Encryptable;

class Store extends BaseModel implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    use HasRoles;
  
    use Authenticatable, Authorizable;

    use Encryptable;
    protected $connection = 'order';

    protected $casts = [
        'store_packing_charges' => 'float',
        'rating' => 'float',
    ];

    protected $encryptable = [
        'email','contact_number'        
    ];

    protected $fillable = [
        'store_type_id','store_name','email', 'password', 'store_location', 'latitude', 'longitude', 'store_zipcode', 'country_id', 'city_id', 'zone_id', 'contact_person', 'contact_number', 'country_code', 'picture', 'store_packing_charges', 'store_gst', 'commission', 'offer_min_amount', 'offer_percent', 'estimated_delivery_time', 'description', 'currency_symbol', 'free_delivery', 'is_veg', 'rating', 'is_bankdetail', 'company_id'
    ];

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'updated_at', 'deleted_at'
     ]; 

    public function scopeSearch($query, $searchText='') {
        return $query
           ->whereHas('storetype', function ($q) use ($searchText){
              $q->where('name', 'like', "%" . $searchText . "%");
           })
          ->orwhere('store_name', 'like', "%" . $searchText . "%")
          ->orwhere('store_location', 'like', "%" . $searchText . "%")
          ->orwhere('contact_number', 'like', "%" .$this->cusencrypt($searchText,env('DB_SECRET')). "%")
          ->orwhere('email', 'like', "%" .$this->cusencrypt($searchText,env('DB_SECRET')). "%"); 
          
            
    }

    public function categories()
    {
        return $this->hasMany('App\Models\Order\StoreCategory', 'store_id', 'id')->where('store_category_status',1);
    }

    public function products()
    {
        return $this->hasMany('App\Models\Order\StoreItem', 'store_id', 'id');
    }
    public function StoreCusinie()
    {
        return $this->hasMany('App\Models\Order\StoreCuisines', 'store_id', 'id');
    }

    public function timings(){
        return $this->hasMany('App\Models\Order\StoreTiming');
    }

    public function storecart()
    {
        return $this->hasMany('App\Models\Order\StoreCart', 'store_id', 'id');
    }
    public function cityprice()
    {
        return $this->hasOne('App\Models\Order\StoreCityPrice', 'store_id', 'id')->select('store_id','delivery_charge');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function storetype()
    {
        return $this->belongsTo('App\Models\Order\StoreType', 'store_type_id', 'id');//->where('status',1);
    }
    
}
