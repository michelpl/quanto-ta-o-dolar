<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BotUser extends Model
{
    protected $fillable = [
        'id',
        'chat_id',
        'first_name',
        'last_nale',
        'username',
        'is_bot',
        'email',
        'status'
    ];
}