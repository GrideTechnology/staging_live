<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class UserVisit extends Model
{
    protected $table = 'user_visits';

    protected $fillable = ['user_id', 'store_id'];

    // Define relationships (if needed)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
