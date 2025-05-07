<?php

namespace App\Models\Order;

use App\Models\BaseModel;

class StoreAddon extends BaseModel
{
    protected $connection = 'order'; 

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];

    public function scopeSearch($query, $searchText='') {
        return $query->where('addon_name', 'like', "%" . $searchText . "%");
	}

	public function storeitem() {
    	  return $this->hasMany('App\Models\Order\StoreItemAddon','store_item_id','id');
    }

	public function itemsaddon() {
    	  return $this->hasMany('App\Models\Order\StoreItemAddon','store_item_id','id');
    }
}	