<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BotUser;
use App\BotCommandList;
use App\BotCommands;
use Accentuation\Accentuation;

class WebhookController extends Controller
{
    protected $botUser;
    protected $botCommands;

    public function __construct(BotUser $botUser) {
        $this->botUser = $botUser;
        $this->botCommands = new BotCommands();
    }

    public function bot(Request $request)
    {
        if (!empty($request->message['chat']['id'])) {

            $message = '';

            if (!isset($request->message['chat']['id'])) {
                return false;
            }

            if (!$this->botUser->find($request->message['from']['id'])) {
               $this->saveBotUser($request->message);
            }

            if ($request->message['text']) {
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
        $botCommands = $this->botCommands;

        $text = Accentuation::remove($message['text']);
        $text = strtolower(str_replace('?', '', $text));
        $text = strtolower(str_replace('/', '', $text));
        $response = BotCommandList::$COMMAND_NOT_FOUND;

        $mainCommand = array_search($text, BotCommandList::$COMMANDS);
        $messageHistory = new MessageHistoryController();

        if ($mainCommand) {
            $messageHistory->disableAllFromUser($chatId);
            return $botCommands->$mainCommand($chatId, $text);
        }

        $command = $messageHistory->getActiveMessageHistoryFromUser($chatId);

        if (!empty($command['steps'])) {
            return $this->executeCommand($command, $chatId, $text);
        }

        $botCommands->sendMessage($response, $chatId);
        return $response;
    }

    private function executeCommand($command, $chatId, $text)
    {
        $botCommands = $this->botCommands;
        $steps = explode(',', $command['steps']);
        $stepIndex = $command['next_step_index']+1;
        if (
            !empty($steps[$stepIndex]) &&
            method_exists($botCommands, $steps[$stepIndex])
        ) {
            $nextCommand = $steps[$stepIndex];

            return
                $botCommands->$nextCommand(
                    $chatId,
                    $text,
                    implode(',', $steps),
                    $stepIndex
                );
        }
    }

    private function saveBotUser($message)
    {
        $this->botUser->id = $message['from']['id'];
        $this->botUser->chat_id = $message['chat']['id'];
        $this->botUser->first_name = $message['from']['first_name'];
        $this->botUser->is_bot = $message['from']['is_bot'];
        $this->botUser->email = null;
        $this->botUser->status = 1;

        if (isset($message['from']['last_name'])) {
            $this->botUser->last_name = $message['from']['last_name'];
        }
        if (isset($message['from']['username'])) {
            $this->botUser->last_name = $message['from']['username'];
        }

        $this->botUser->save();
    }

    public function check(Request $request)
    {
        return $request;
    }
}
