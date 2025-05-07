<?php

namespace App\Models\Order;

use App\Models\BaseModel;

class StoreItem extends BaseModel
{
    protected $connection = 'order';

    protected $casts = [
        'item_price' => 'float',
        'item_discount' => 'float',
    ];

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];

      public function scopeSearch($query, $searchText='') {
        return $query
          ->where('item_name', 'like', "%" . $searchText . "%")
          ->orwhere('item_description', 'like', "%" . $searchText . "%");
              
      }

      public function itemsaddon() {
    	  return $this->hasMany('App\Models\Order\StoreItemAddon','store_item_id','id');
      }

      public function store() {
        return $this->hasOne('App\Models\Order\Store','id','store_id')->select('store_name','store_packing_charges','store_gst','commission','offer_min_amount','offer_percent','free_delivery','id','rating','estimated_delivery_time');
      }

      public function itemcart() {
        return $this->hasMany('App\Models\Order\StoreCart','store_item_id','id');
      }

      public function itemcartaddon() {
        return $this->hasMany('App\Models\Order\StoreCartItemAddon','store_cart_item_id','id')->select('store_item_addons_id','store_cart_item_id');
      }
      public function categories()
      {
          return $this->hasMany('App\Models\Order\StoreCategory','id','store_category_id');
      }

}
