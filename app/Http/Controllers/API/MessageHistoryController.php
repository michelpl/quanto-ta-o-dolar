<?php

namespace App\Http\Controllers\API;

use App\MessageHistory as MessageHistory;
use App\Http\Controllers\Controller;

class MessageHistoryController extends Controller
{
    protected $messageHistory;

    public function __construct()
    {
        $this->messageHistory = new MessageHistory();
    }

    public function create(
        $chatId,
        $message,
        $command,
        $steps,
        $nextStepIndex
    )
    {
        try {
            $this->disableAllFromUser($chatId);

            $this->messageHistory->chat_id = $chatId;
            $this->messageHistory->command = $command;
            $this->messageHistory->steps = $steps;
            $this->messageHistory->next_step_index = $nextStepIndex;
            $this->messageHistory->message = $message;
            $this->messageHistory->status = 1;
            $this->messageHistory->save();

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function disableAllFromUser($chatId)
    {
        $this->messageHistory
            ->where('chat_id', $chatId)
            ->update(['status' => 0]);
    }

    public function getActiveMessageHistoryFromUser($chatId)
    {
        return
            $this
                ->messageHistory
                ->where([
                    ['chat_id', $chatId],
                    ['status', 1]
                ])->first();
    }
}