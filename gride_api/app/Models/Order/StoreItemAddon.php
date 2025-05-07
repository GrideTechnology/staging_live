<?php

namespace App\Models\Order;

use App\Models\BaseModel;

class StoreItemAddon extends BaseModel
{
    protected $connection = 'order';

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];

      public function item()
    {
        return $this->belongsTo('App\Models\Order\StoreItem', 'store_item_id', 'id');
    }
    public function addon() {
    	  return $this->hasOne('App\Models\Order\StoreAddon','id','store_addon_id')->select('id','addon_name');
    }
}
