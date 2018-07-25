<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'city',
        'required_amount',
        'required_price',
        'current_price',
        'status'
    ];
}
