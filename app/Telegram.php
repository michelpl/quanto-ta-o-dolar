<?php

namespace App;
use Telegram\Bot\Api;

class Telegram
{
    public function sendMessage($chatId, $message)
    {
        $telegram = new Api(config('telegram.bot_token'));
        return $telegram->sendMessage(
            ['chat_id'=> $chatId, 'text' => $message]
        );
    }
}
