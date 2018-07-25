<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BotUser;
use App\BotCommandList;
use App\BotCommands;
use App\Telegram;

class WebhookController extends Controller
{
    protected $botUser;

    public function __construct(BotUser $botUser) {
        $this->botUser = $botUser;
    }

    public function index()
    {
        //
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
                    $request->message['text'],
                    $request->message['from']['id']
                );
            }

            if (strlen($message) > 0) {
                $this->sendMessage($message, $request->message['chat']['id']);
            }

            return $message;
        }
    }

    private function executeBotCommand($text, $userId)
    {
        $city = 'rio-de-janeiro';
        $requiredAmount = 500;
        $requiredPrice = 3.85;

        $command = array_search($text, BotCommandList::$COMMANDS);
        $botCommands = new BotCommands();
        $message = BotCommandList::$COMMAND_NOT_FOUND;

        if ($command) {
            switch ($command)
            {
                case 'howMuchIsDolar' :
                    $message = $botCommands
                        ->howMuchIsDolar(
                            $city,
                            $requiredAmount
                        );
                    break;
                case 'createPriceAlert' :
                    $message = $botCommands->createPriceAlert(
                        $userId,
                        $requiredPrice,
                        $city,
                        $requiredAmount
                    );
                    break;
            }
        }

        return $message;
    }

    private function sendMessage($message, $chatId)
    {
        $telegram = new Telegram();
        $telegram->sendMessage($chatId, $message);
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
}
