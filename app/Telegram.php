<?php

namespace App;
use Telegram\Bot\Api;

class Telegram
{
    private $telegram;

    public function __construct()
    {
        $this->telegram = new Api(config('telegram.bot_token'));
    }

    public function sendMessage($chatId, $message, $keyboard = null, $messageId = null)
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $message,
            'reply_to_message_id' => $messageId
        ];

        if ($keyboard) {
            $reply_markup = $this->telegram->replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);

            $params['reply_markup'] = $reply_markup;
        }

        $this->telegram->sendMessage($params);
    }
}
