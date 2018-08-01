<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'chat_id',
        'email',
        'city',
        'required_amount',
        'required_price',
        'current_price',
        'status'
    ];
}
