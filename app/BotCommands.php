<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\API\PriceController;
use App\Http\Controllers\API\PriceRulesController;
use App\Alert as AlertModel;
use App\BotCommandList;
use App\MessageHistory;


class BotCommands extends Model
{
    private function createMessageHistory(
        $chatId,
        $command,
        $nextCommand = null,
        $message = null
    )
    {
        try {
            $messageHistory = new MessageHistory();

            $messageHistory->chat_id = $chatId;
            $messageHistory->command = $command;
            $messageHistory->nextCommand = $nextCommand;
            $messageHistory->message = $message;
            $messageHistory->status = 1;
            $messageHistory->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

    }

    private function disableMessageHistoryFromUser($chatId)
    {
        $messageHistory = new MessageHistory();
        $messageHistory
            ->where('chat_id', $chatId)
            ->update(['status' => 0]);
    }


    public function howMuchIsDollar($chatId, $text)
    {
        $message = "Em qual cidade deseja comprar?";
        $nextCommand = 'howMuchIsDollarCityResponse';
        $keyboard = $this->getCityKeyboard();

        return
            $this->createMessageHistoryAndSendIt(
                $chatId,
                $message,
                $nextCommand,
                $keyboard,
                $text
            );
    }

    public function howMuchIsDollarCityResponse($chatId, $text)
    {
        $message = 'Qual valor deseja comprar?';
        $nextCommand = 'buyByCity';
        $keyboard = $this->getValueKeyboard();
        return
            $this->createMessageHistoryAndSendIt(
                $chatId,
                $message,
                $nextCommand,
                $keyboard,
                $text
            );
    }

    public function buyByCity($chatId, $text)
    {

        if (!empty(PriceRulesController::$values[ucfirst($text)])) {
            $amountTobuy = PriceRulesController::$values[ucfirst($text)];

            $priceController = new PriceController();

            $nextCommand = 'buyByCity';
            $history = $this->getMessageHistoryByNextCommand($chatId, $nextCommand);
            $city = str_replace(' ', '-', $history['message']);

            $price = $priceController->buyByCity($city, $amountTobuy);

            $message = $this->messageHowMuch(
                $price,
                City::$CITIES[$city][0]
            );

            $this->createMessageHistoryAndSendIt(
                $chatId,
                $message,
                $nextCommand,
                null,
                $text
            );
        }

        return 'Comando não encontrado';
    }


    private function createMessageHistoryAndSendIt(
        $chatId,
        $message,
        $nextCommand,
        $keyboard,
        $text
    )
    {
        try {
            $this->disableMessageHistoryFromUser($chatId);

            $this->createMessageHistory(
                $chatId,
                __FUNCTION__,
                $nextCommand,
                $text
            );

            $this->sendMessage($message, $chatId, $keyboard);

            return $message;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function getMessageHistoryByNextCommand($chatId, $nextCommand)
    {
        $messageHistory = new MessageHistory();
        return $messageHistory
            ->where('chat_id', $chatId)
            ->where('nextCommand', $nextCommand)
            ->latest()
            ->first()
        ;
    }

    protected function sendMessage($message, $chatId, $keyboard = null)
    {
        $telegram = new Telegram();
        return $telegram->sendMessage($chatId, $message, $keyboard);
    }

    public function createPriceAlert($userId, $chatId)
    {
        $priceController = new PriceController();
        $alert = new AlertModel();

        return $this->sendResponseFromCreatePriceAlert($chatId);

        $currentPrice = $priceController->buyByCity($city, $requiredAmount);

        $alert->user_id = $userId;
        $alert->city = $city;
        $alert->required_amount = $requiredAmount;
        $alert->required_price = $requiredPrice;
        $alert->current_price = $currentPrice['lower_price'];
        $alert->status = 1;
        $alert->save();
        return $this->messageCreateAlert($requiredPrice, $city, $requiredAmount);
    }

    protected function messageCreateAlert($requiredPrice, $city, $requiredAmount)
    {
        return
            'Alerta criado para compra de $' . $requiredAmount . '.00 na cidade ' .
            $city . ' quando o dolar chegar a R$' . $requiredPrice;
        ;
    }

    protected function getCityKeyboard()
    {
        $keyboard = [];

        foreach (City::$CITIES as $key => $city) {
            $keyboard[] = $city;
        }

        return $keyboard;
    }

    protected function getValueKeyboard()
    {
        $keyboard = [];

        foreach (PriceRulesController::$values as $index => $value) {
            $keyboard[] = [$index];
        }

        return $keyboard;
    }

    protected function messageHowMuch($price, $city)
    {
        return
            'Para a compra de: $' . $price['amout_to_buy'] . '.00' . "\n" .
            'O preço oficial é de: R$' . $price['original_price'] .
            ' na cidade ' . $city . "\n" .
            'Você pode fazer uma oferta de: R$' . $price['lower_price'] . "\n" .
            'Preço consultado em: ' .
            $price['date_time'] . "\n" .
            '*O preço pode variar conforme dia e horário da consulta';
        ;
    }
}
