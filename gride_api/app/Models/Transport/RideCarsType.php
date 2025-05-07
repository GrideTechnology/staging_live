<?php

namespace App\Models\Transport;

use App\Models\BaseModel;

class RideCarsType extends BaseModel
{
    protected $connection = 'transport';

    protected $hidden = [
     	'created_at', 'updated_at'
    ];

    public function scopeSearch($query, $searchText='') {
        return $query
            ->where('name', 'like', "%" . $searchText . "%");
    }
}
