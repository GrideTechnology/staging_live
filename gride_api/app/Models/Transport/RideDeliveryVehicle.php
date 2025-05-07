<?php

namespace App\Models\Transport;

use App\Models\BaseModel;

class RideDeliveryVehicle extends BaseModel
{
    protected $connection = 'transport';

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function scopeSearch($query, $searchText='') {
        return $query
            ->where('vehicle_name', 'like', "%" . $searchText . "%")
            ->orWhere('vehicle_type', 'like', "%" . $searchText . "%")
            ->orWhere('ride_type_id', 'like', "%" . $searchText . "%");
    }

    public function ride_type()
    {
        return $this->belongsTo('App\Models\Transport\RideType');
    }

    public function ride()
    {
        return $this->has('App\Models\Common\ProviderService');
    }
    
    public function vehicle_type()
    {
        return $this->has('App\Models\Common\ProviderVehicle', 'vehicle_service_id');
    }



    public function priceDetails()
    {
        return $this->belongsTo('App\Models\Transport\RideCityPrice', 'id', 'ride_delivery_vehicle_id')->select('ride_delivery_vehicle_id','calculator','fixed','price','minute','hour','distance');
    }
}
