<?php

namespace App\Models\Order;

use App\Models\BaseModel;
use Auth;

class StoreType extends BaseModel
{
    protected $connection = 'order';

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];

      public function storetype() {
    	return $this->hasMany('App\Models\Order\Cuisine');
    }
     public function providerservice() {
    return $this->hasOne('App\Models\Common\ProviderService','category_id','id')->where('admin_service','ORDER')->where('provider_id',Auth::guard('provider')->user()->id)->with('providervehicle');
    }

    public function provideradminservice() {
        return $this->hasOne('App\Models\Common\ProviderService','category_id','id')->where('admin_service','ORDER');
    }

      public function scopeSearch($query, $searchText='') {
        return $query
            ->where('name', 'like', "%" . $searchText . "%");
            
    }
}
