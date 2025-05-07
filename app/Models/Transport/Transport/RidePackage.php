<?php

namespace App\Models\Transport;

use App\Models\BaseModel;

class RidePackage extends BaseModel
{
    protected $connection = 'transport';

    protected $hidden = [
        'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'updated_at'
    ];

    public function scopeSearch($query, $searchText='') {
        $word = 'enable';
        $word2 = 'disable';
        if (strpos($word, $searchText) !== FALSE) {
            return $query
                ->where('package_name', 'like', "%" . $searchText . "%")
                ->orWhere('status',1);
        }else if (strpos($word2, $searchText) !== FALSE) {   
            return $query
            ->where('package_name', 'like', "%" . $searchText . "%")
            ->orWhere('status',0);
        }else{
            return $query
            ->where('package_name', 'like', "%" . $searchText . "%");
        }
    }

}
