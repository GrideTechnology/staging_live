<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class UserRequest extends BaseModel
{
    protected $connection = 'common';

	protected $appends = ['request'];

	protected $hidden = [
     	'request_data', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'updated_at', 'deleted_at'
     ];
    /**
     * The user who created the request.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Common\User');
    }

   

    public function service()
    {
        return $this->belongsTo('App\Models\Common\AdminService', 'admin_service', 'admin_service');
    }

    /**
     * The provider assigned to the request.
     */
    public function provider()
    {
        return $this->belongsTo('App\Models\Common\Provider', 'provider_id');
    }

    public function getRequestAttribute() {
        return json_decode($this->attributes['request_data']);
        
    }
}
