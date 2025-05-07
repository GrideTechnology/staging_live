<?php

namespace App\Models\Order;

use App\Models\BaseModel;

class StoreWallet extends BaseModel
{
    protected $connection = 'order';

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'updated_at', 'deleted_at'
     ];

     public function scopeSearch($query, $searchText='') {
        return $query
            ->where('transaction_alias', 'like', "%" . $searchText . "%")
            ->orWhere('transaction_desc', 'like', "%" . $searchText . "%") 
            ->orWhere('amount', 'like', "%" . $searchText . "%") 
            ->orWhere('type', 'like', "%" . $searchText . "%");
    }

    public function storesDetails()
    {
       return $this->hasOne('App\Models\Order\Store', 'id','store_id');
    }

    public function order()
    {
       return $this->hasOne('App\Models\Order\StoreOrder', 'id','transaction_id');
    }
}
