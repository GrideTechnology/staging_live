<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class AccountDeleteRequest extends BaseModel
{
	protected $connection = 'common';
	
    protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    protected $fillable = [

        'name', 'email','mobile', 'message'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
   

    public function scopeSearch($query, $searchText='') {
        return $query
            ->where('name', 'like', "%" . $searchText . "%")
            ->orWhere('email', 'like', "%" . $searchText . "%");
    }
}
