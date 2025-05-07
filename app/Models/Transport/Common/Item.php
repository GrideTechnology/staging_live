<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $connection = 'common';

    protected $fillable = [
        'user_id', 'item_id', 'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
