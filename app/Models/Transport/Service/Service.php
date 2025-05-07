<?php

namespace App\Models\Service;

use App\Models\BaseModel;
use Auth;

class Service extends BaseModel
{
    protected $connection = 'service';

   

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];

 public function scopeSearch($query, $searchText='') {
        return $query
                        ->whereHas('serviceCategory', function ($q) use ($searchText){
                            $q->where('service_category_name', 'like', "%" . $searchText . "%");
                        })
                        ->orwhereHas('servicesubCategory', function ($q) use ($searchText){
                            $q->where('service_subcategory_name', 'like', "%" . $searchText . "%");
                        })
                        ->orWhere('service_name', 'like', "%" . $searchText . "%");
    }
    public function serviceCategory()
    {
        return $this->belongsTo('App\Models\Service\ServiceCategory','service_category_id',"id");
    }
    public function servicesubCategory()
    {
        return $this->belongsTo('App\Models\Service\ServiceSubcategory','service_subcategory_id','id');
    }
    public function subCategories()
    {
        return $this->hasMany('App\Models\Service\ServiceSubcategory', 'id','service_subcategory_id');
    }

    public function providerservices() {
    return $this->hasMany('App\Models\Common\ProviderService','service_id','id')->where('admin_service','SERVICE')->where('provider_id',Auth::guard('provider')->user()->id);
    }

    public function provideradminservice() {
        return $this->hasOne('App\Models\Common\ProviderService','service_id','id')->where('admin_service','SERVICE');
    }

    public function servicescityprice() {
        return $this->hasone('App\Models\Service\ServiceCityPrice','id','service_id');
    }
    public function service_city() {
        return $this->belongsTo('App\Models\Service\ServiceCityPrice','id','service_id');
    }



}
