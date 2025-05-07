<?php

namespace App\Models\Transport;

use App\Models\BaseModel;

class RideCityPrice extends BaseModel
{
    protected $connection = 'transport';

    protected $casts = [
        'fixed' => 'float',
        'price' => 'float',
        'minute' => 'float',
        'hour' => 'float',
        'distance' => 'float',
        'package_name' => 'float',
        'default_price' => 'float',
        'cancellation_fee' => 'float',
        'max_price' => 'float',
        'min_price' => 'float',
        'service_fee' => 'float',
        'airport_fee' => 'float',
        'scheduled_cancellation_fee' => 'float',
        'scheduled_minimum_fee' => 'float',
        'base_price' => 'float',
        'base_unit' => 'float',
        'base_hour' => 'float',
        'per_unit_price' => 'float',
        'per_minute_price' => 'float',
        'per_km_price' => 'float',
        'per_hour' => 'float',
        'per_hour_distance' => 'float',
        'waiting_free_mins' => 'float',
        'waiting_min_charge' => 'float',
        'commission' => 'float',
        'fleet_commission' => 'float',
        'tax' => 'float',
        'peak_commission' => 'float',
        'waiting_commission' => 'float'
    ];

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];
   
    public function ridePeakhour()
    {
        return $this->hasMany('App\Models\Transport\RidePeakPrice', 'ride_city_price_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\Common\City', 'city_id', 'id');
    }

     public function ridetype()
    {
       return $this->belongsTo('App\Models\Common\CompanyCity', 'city_id','city_id');
    }

     public function ridedelivery_type()
    {
       return $this->belongsTo('App\Models\Transport\RideDeliveryVehicle','ride_delivery_vehicle_id','id');
    }


}