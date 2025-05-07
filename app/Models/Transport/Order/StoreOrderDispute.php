<?php

namespace App\Models\Order;

use App\Models\BaseModel;

class StoreOrderDispute extends BaseModel
{
    protected $connection = 'order';
    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'updated_at', 'deleted_at'
     ];
    
    public function scopeSearch($query, $searchText='') {
        if ($searchText != '') {
            $result =  $query
            ->where('dispute_type', 'like', "%" . $searchText . "%")
            ->orWhere('status', 'like', "%" . $searchText . "%")
            ->orWhere('comments', 'like', "%" . $searchText . "%")
            ->orWhere('dispute_name', 'like', "%" . $searchText . "%")
            ->orwhereHas('request', function ($q) use ($searchText){
                            $q->where('store_order_invoice_id', 'like', "%" . $searchText . "%");
                        });
        }
        return $result;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Common\User');
    }

    /**
     * The provider assigned to the request.
     */
    public function provider()
    {
        return $this->belongsTo('App\Models\Common\Provider', 'provider_id');
    }

    public function request()
    {
        return $this->belongsTo('App\Models\Order\StoreOrder','store_order_id');
    }

    
}
