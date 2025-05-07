<?php

namespace App\Models\Transport;

use App\Models\BaseModel;

class RideChat extends BaseModel
{
    protected $connection = 'transport';

    protected $hidden = [
     	'created_at'
    ];

   }
