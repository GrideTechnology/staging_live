<?php

namespace App\Models\Order;

use App\Models\BaseModel;

class StoreCartItemAddon extends BaseModel
{
    protected $connection = 'order';

    protected $casts = [
        'addon_price' => 'float'
    ];

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];

    public function addon()
    {
        return $this->hasOne('App\Models\Order\StoreItemAddon','id','store_item_addons_id');
    }
}
