<?php

namespace App\Models\Transport;

use App\Models\BaseModel;

class RideCars extends BaseModel
{
    protected $connection = 'transport';

    protected $hidden = [
     	'created_at'
    ];

    public function scopeSearch($query, $searchText='') {
        return $query
            ->where('model', 'like', "%" . $searchText . "%");
    }
}
