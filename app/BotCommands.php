<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\API\PriceController;
use App\Http\Controllers\API\PriceRulesController;
use App\Http\Controllers\API\MessageHistoryController;
use App\Alert as AlertModel;

class BotCommands extends Model
{
    /***************** Main commands ***********************/

    public function help($chatId)
    {
        $response = BotCommandList::$HELP;
        $keyboard = [];
        foreach (BotCommandList::$HELP_COMMANDS as $command) {
            $keyboard[] = [$command];
        }

        $this->sendMessage($response, $chatId, $keyboard);
        return $response;
    }

    public function start($chatId) {
        return $this->help($chatId);
    }

    public function cancel() {
        return;
    }

    public function howMuchIsDollar($chatId, $text)
    {
        $response = "Em qual cidade deseja comprar?";
        $steps = __FUNCTION__ . ',valueResponse,priceResponse';
        $nextStepIndex = '0';
        $keyboard = $this->getCityKeyboard();

        try {
            $messageHistory = new MessageHistoryController();
            $messageHistory->create(
                $chatId,
                $text,
                __FUNCTION__,
                $steps,
                $nextStepIndex
            );

            $this->sendMessage($response, $chatId, $keyboard);

            return $response;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function createPriceAlert($chatId, $text)
    {
        $response = "Em qual cidade deseja comprar?";
        $steps = __FUNCTION__ . ',valueResponse,requiredPriceResponse,createAlert';
        $nextStepIndex = '0';
        $keyboard = $this->getCityKeyboard();

        try {
            $messageHistory = new MessageHistoryController();
            $messageHistory->create(
                $chatId,
                $text,
                __FUNCTION__,
                $steps,
                $nextStepIndex
            );

            $this->sendMessage($response, $chatId, $keyboard);

            return $response;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /***************** Aux commands ***********************/

    /**
     * Send a message with a value keyboard to
     * user who sends the city and save it on
     * message history
     *
     * @param $chatId
     * @param $text
     * @param $mainCommand
     * @return string
     */
    public function valueResponse($chatId, $text, $steps, $stepIndex)
    {
        $response  = BotCommandList::$COMMAND_NOT_FOUND;
        $city = str_replace(' ', '-', $text);

        if (!empty(City::$CITIES[$city][0])) {
            $response = "Qual valor deseja comprar?";

            $keyboard = $this->getValueKeyboard();

            try {
                $messageHistory = new MessageHistoryController();
                $messageHistory->create(
                    $chatId,
                    $text,
                    __FUNCTION__,
                    $steps,
                    $stepIndex
                );

                $this->sendMessage($response, $chatId, $keyboard);

                return $response;

            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        $this->sendMessage($response, $chatId);
        return $response;
    }

    /**
     * @param $chatId
     * @param $text
     * @param $mainCommand
     * @return string
     */
    public function priceResponse($chatId, $text, $steps, $stepIndex)
    {
        $response = BotCommandList::$COMMAND_NOT_FOUND;

        if (!empty(PriceRulesController::$values[ucfirst($text)])) {

            $city = $this->getCityFromHistory($chatId);
            $price = $this->getPrice($text, $city);

            $response = $this->messageHowMuch(
                $price,
                City::$CITIES[$city][0]
            );

            try {
                $messageHistory = new MessageHistoryController();
                $messageHistory->create(
                    $chatId,
                    $text,
                    __FUNCTION__,
                    $steps,
                    $stepIndex
                );

                $this->sendMessage($response, $chatId);

                return $response;

            } catch (\Exception $e) {
                return $e->getMessage();
            }

        }

        $this->sendMessage($response, $chatId);
        return $response;
    }

    public function requiredPriceResponse($chatId, $text, $steps, $stepIndex)
    {
        if (!empty(PriceRulesController::$values[ucfirst($text)])) {
            $response = "Você deseja ser alertado quando o dólar chegar a qual valor em reais?";

            try {
                $messageHistory = new MessageHistoryController();
                $messageHistory->create(
                    $chatId,
                    $text,
                    __FUNCTION__,
                    $steps,
                    $stepIndex
                );

                $this->sendMessage($response, $chatId);

                return $response;

            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    public function createAlert($chatId, $text, $steps, $stepIndex)
    {
        $priceController = new PriceController();
        $alert = new AlertModel();

        $previousMethod = 'requiredPriceResponse';
        $city = $this->getCityFromHistory($chatId);
        $history = $this->getMessageHistoryByCommand($chatId, $previousMethod);
        $requiredAmount = $this->fromCurrencyToFLoat($history['message']);
        $requiredPrice = $this->fromCurrencyToFLoat($text);

        $currentPrice = $priceController->buyByCity($city, $requiredAmount);

        $response = $this->messageCreateAlert(
            $requiredPrice,
            $city,
            $requiredAmount
        );

        try {
            $alert->chat_id = $chatId;
            $alert->city = $city;
            $alert->required_amount = $requiredAmount;
            $alert->required_price = $requiredPrice;
            $alert->current_price = $currentPrice['lower_price'];
            $alert->status = 1;
            $alert->save();

            $messageHistory = new MessageHistoryController();
            $messageHistory->create(
                $chatId,
                $text,
                __FUNCTION__,
                $steps,
                $stepIndex
            );

            $this->sendMessage($response, $chatId);

            return $response;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /***************** Aux methods ***********************/

    private function getMessageHistoryByCommand($chatId, $command)
    {
        $messageHistory = new MessageHistory();
        return $messageHistory
            ->where('chat_id', $chatId)
            ->where('command', $command)
            ->latest()
            ->first()
        ;
    }

    public function sendMessage($message, $chatId, $keyboard = null)
    {
        $telegram = new Telegram();
        return $telegram->sendMessage($chatId, $message, $keyboard);
    }

    protected function messageCreateAlert($requiredPrice, $city, $requiredAmount)
    {
        return
            'Alerta criado para compra de $' .
            $requiredAmount .
            ' na cidade ' .
            City::$CITIES[$city][0] .
            ' quando o dolar chegar a R$' .
            $requiredPrice;
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

    protected function getCityFromHistory($chatId)
    {
        $previousCommand = 'valueResponse';
        $history =
            $this->getMessageHistoryByCommand($chatId, $previousCommand);
        return str_replace(' ', '-', $history['message']);
    }

    protected function getPrice($text, $city)
    {
        $amountTobuy = PriceRulesController::$values[ucfirst($text)];
        $priceController = new PriceController();

        return $priceController->buyByCity($city, $amountTobuy);
    }

    protected function messageHowMuch($price, $city)
    {
        return
            'Para a compra de: $' . $price['amout_to_buy'] . '.00' . "\n" .
            'O preço oficial é de: R$' . $price['original_price'] .
            ' nas casas de câmbio na cidade ' . $city . "\n" .
            'Você pode fazer uma oferta de: R$' . $price['lower_price'] . "\n" .
            'Preço consultado em: ' .
            $price['date_time'] . "\n" .
            '*O preço pode variar conforme dia e horário da consulta';
        ;
    }

    protected function fromCurrencyToFLoat($text)
    {
        $amount = str_replace('r$', '', $text);
        return (float) str_replace(',', '.', $amount);
    }
}
