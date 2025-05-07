<?php

namespace App\Models\Transport;
use App\Models\BaseModel;

class RideRequestWaitingTime extends BaseModel
{
    protected $connection = 'transport';

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];
}
