<?php

namespace App\Models\Common;

use App\Models\BaseModel;
use Auth;

class CompanyDocument extends BaseModel
{
	protected $connection = 'common';
	
    protected $fillable = [
        'name',
        'file_type',
    ];

    protected $hidden = [
     	'created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function scopeSearch($query, $searchText='') {
        return $query
            ->where('name', 'like', "%" . $searchText . "%");
    }
    public function provider_document()
    {
        return $this->belongsTo('App\Models\Common\ProviderDocument', 'id', 'document_id')->where('provider_id',Auth::guard('provider')->user()->id);
    }

    // public function service()
    // {
    //    return $this->belongsTo('App\Models\Common\AdminService', 'service', 'admin_service');
    // }
}
