<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BotUser;
use App\BotCommandList;
use App\BotCommands;
use App\Telegram;
use Accentuation\Accentuation;
use App\MessageHistory;

class WebhookController extends Controller
{
    protected $botUser;

    public function __construct(BotUser $botUser) {
        $this->botUser = $botUser;
    }

    public function bot(Request $request)
    {
        if (!empty($request->message['chat']['id'])) {

            $message = '';

            if (!$this->botUser->find($request->message['from']['id'])) {
               $this->saveBotUser($request->message);
            }

            if (
                !empty($request->message['text']) &&
                (
                    strtolower($request->message['text']) === '/start' ||
                    strtolower($request->message['text']) === 'help'
                )
            ) {
                $message = $this->getCommandList();

            } elseif ($request->message['text']) {
                $message = $this->executeBotCommand(
                    $request->message,
                    $request->message['chat']['id']
                );
            }

            return $message;
        }
    }

    private function executeBotCommand($message, $chatId)
    {
        $botCommands = new BotCommands();

        $text = Accentuation::remove($message['text']);
        $text = strtolower(str_replace('?', '', $text));

        $mainCommand = array_search($text, BotCommandList::$COMMANDS);

        if ($mainCommand) {
            $this->disableMessageHistoryFromUser($chatId);
            return $botCommands->$mainCommand($chatId, $text);
        }

        $command = $this->getActiveMessageHistoryFromUser($chatId);
        $nextCommand = $command['nextCommand'];

        $message = $botCommands->$nextCommand($chatId, $text);

        return $message;
    }

    private function saveBotUser($message)
    {
        $this->botUser->id = $message['from']['id'];
        $this->botUser->chat_id = $message['chat']['id'];
        $this->botUser->first_name = $message['from']['first_name'];
        $this->botUser->last_name = $message['from']['last_name'];
        $this->botUser->username = $message['from']['username'];
        $this->botUser->is_bot = $message['from']['is_bot'];
        $this->botUser->email = null;
        $this->botUser->status = 1;

        $this->botUser->save();
    }

    private function getCommandList()
    {
        return BotCommandList::$HELP;
    }

    public function check(Request $request)
    {
        return $request;
    }

    private function disableMessageHistoryFromUser($chatId)
    {
        $messageHistory = new MessageHistory();
        $messageHistory
            ->where('chat_id', $chatId)
            ->update(['status' => 0]);
    }

    private function getActiveMessageHistoryFromUser($chatId)
    {
        $messageHistory = new MessageHistory();
        return
            $messageHistory
            ->where([
                ['chat_id', $chatId],
                ['status', 1]
            ])->first();
    }
}
