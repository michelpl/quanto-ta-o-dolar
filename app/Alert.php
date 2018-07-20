<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'email',
        'required_price',
        'current_price',
        'status'
    ];
}
