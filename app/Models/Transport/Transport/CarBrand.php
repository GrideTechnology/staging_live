<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;

class CarBrand extends Model
{
    protected $connection = 'transport';
    protected $table = 'car_brand_master';

    public $timestamps = true;

    protected $dateFormat = 'U';

    protected $fillable = [
       'ride_delivery_vehicles_id','brand_name','slug','is_active','is_deleted'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function ride() {
    	return $this->hasOne('App\Models\Transport\RideDeliveryVehicle');
    }

    public static function slugExist($slug, $_make_slug, $number = 1) {
        $make_slug = self::where(['slug' => $slug])->first();
        if (!empty($make_slug->brand_name) && !empty($make_slug->brand_name)) {
            return self::slugExist($_make_slug . $number, $_make_slug, $number + 1);
        } else {
            return $slug;
        }
    }
}
