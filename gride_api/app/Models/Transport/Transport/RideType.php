<?php

namespace App\Models\Transport;

use App\Models\BaseModel;
use Auth;

class RideType extends BaseModel
{
    protected $connection = 'transport';

    protected $fillable = [
        'company_id','service_type', 'ride_name','status'
    ];

    protected $hidden = [
     	'company_id', 'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function ride() {
    	return $this->hasMany('App\Models\Transport\RideDeliveryVehicle');
    }

    public function servicelist() {
        if(Auth::guard('provider')->user()->gender == 'MALE'){
            return $this->hasMany('\App\Models\Transport\RideDeliveryVehicle','ride_type_id','id')->where(['status' => 1, 'is_deleted' => 0])->where('is_female',0)->whereNotIn('vehicle_name',['Gride Female','Gride Care']);
        } else {
            return $this->hasMany('\App\Models\Transport\RideDeliveryVehicle','ride_type_id','id')->where(['status' => 1, 'is_deleted' => 0])->whereIn('is_female',[0,1])->whereNotIn('vehicle_name',['Gride Female','Gride Care']);
        }
    }

    public function providerservice() {
        return $this->hasOne('App\Models\Common\ProviderService','category_id','id')->whereIn('admin_service',['TRANSPORT','ORDER'])->where('provider_id',Auth::guard('provider')->user()->id)->with('providervehicle');
    }

    public function provideradminservice() {
        return $this->hasOne('App\Models\Common\ProviderService','category_id','id')->where('admin_service','TRANSPORT');
    }

    public function scopeSearch($query, $searchText='') {
        return $query
            ->where('ride_name', 'like', "%" . $searchText . "%");
          
    }
}
