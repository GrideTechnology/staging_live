<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class NotifiedProvider extends BaseModel
{
    protected $connection = 'common';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider_id',
        'request_id',
    ];
  
}
