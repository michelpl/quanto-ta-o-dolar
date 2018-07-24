<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'required_price',
        'current_price',
        'status'
    ];
}
