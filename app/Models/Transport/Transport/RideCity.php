<?php

namespace App\Models\Transport;

use App\Models\BaseModel;

class RideCity extends BaseModel
{
    protected $connection = 'transport';

    protected $casts = [
        'comission' => 'float',
        'fleet_comission' => 'float',
        'tax' => 'float',
        'surge' => 'float',
        'night_charges' => 'float',
        'driver_beta_amount' => 'float',
        'fleet_commission' => 'float',
        'waiting_percentage' => 'float',
        'peak_percentage' => 'float',
        'commission' => 'float',
        'fleet_commission' => 'float'
    ];

    protected $hidden = [
     	'company_id','created_type', 'created_by', 'modified_type', 'modified_by', 'deleted_type', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
     ];
}
