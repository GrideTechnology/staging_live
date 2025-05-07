<?php

namespace App\Models\Transport;

use App\Models\BaseModel;

class RideLostItem extends BaseModel
{
    protected $connection = 'transport';

    public function user()
    {
        return $this->belongsTo('App\Models\Common\User', 'user_id', 'id');
    }

    public function user1()
    {
        return $this->belongsTo('App\Models\Common\User', 'id', 'user_id');
    }

    public function riderequests()
    {
        return $this->belongsTo('App\Models\Transport\RideRequest', 'ride_request_id', 'id');
    }

      public function scopeSearch($query, $searchText='') {
      return 
          $query
        ->whereHas('riderequests',function($q) use ($searchText){
             $q-> where('booking_id','like',"%" . $searchText . "%");
          })->orwhereHas('user1',function($q) use ($searchText){
             $q->where('first_name','like',"%" . $searchText . "%");
          })  

        ->orwhere('lost_item_name', 'like', "%" . $searchText . "%")
        ->orwhere('comments', 'like', "%" . $searchText . "%")
        ->orwhere('status', 'like', "%" . $searchText . "%");
          
       
          
    }
   
}