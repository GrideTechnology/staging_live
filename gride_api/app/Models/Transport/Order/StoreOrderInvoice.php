<?php

namespace App\Models\Order;

use App\Models\BaseModel;

class StoreOrderInvoice extends BaseModel
{
    protected $connection = 'order';

	protected $appends = ['items'];

	protected $casts = [
        'gross' => 'float',
        'net' => 'float',
        'discount' => 'float',
        'promocode_id' => 'float',
        'promocode_amount' => 'float',
        'wallet_amount' => 'float',
        'tax_per' => 'float',
        'tax_amount' => 'float',
        'commision_per' => 'float',
        'commision_amount' => 'float',
        'delivery_per' => 'float',
        'delivery_amount' => 'float',
        'store_package_amount' => 'float',
        'total_amount' => 'float',
        'cash' => 'float',
        'payable' => 'float',
        'subtotal' => 'float',
        'small_order_fee' => 'float',
        'gride_commission_per' => 'float',
        'gride_commission_amount' => 'float',
        'cancellation_fee' => 'float',
        'cancellation_fee_goesto' => 'integer',
    ];

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];


    public function getItemsAttribute() {
        return json_decode($this->attributes['cart_details']);
        
    }
}
