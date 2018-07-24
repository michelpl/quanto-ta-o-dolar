<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\API\PriceController;
use App\Alert as AlertModel;

class BotCommands extends Model
{
    public function howMuchIsDolar($city, $amountToBuy)
    {
        $priceController = new PriceController();
        $price = $priceController->buyByCity($city, $amountToBuy);

        $message = $this->messageHowMuch($price, $city);

        return $message;
    }

    public function createPriceAlert($userId, $requiredPrice, $city, $amountToBuy)
    {
        $priceController = new PriceController();
        $alert = new AlertModel();

        $currentPrice = $priceController->buyByCity($city, $amountToBuy);

        $alert->user_id = $userId;
        $alert->required_price = $requiredPrice;
        $alert->current_price = $currentPrice['lower_price'];
        $alert->status = 1;
        $alert->save();

        return $this->messageCreateAlert($requiredPrice, $city, $amountToBuy);
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
            '*O preço pode variar conforme horário da consulta';
        ;
    }

    protected function messageCreateAlert($requiredPrice, $city, $amountToBuy)
    {
        return
            'Alerta criado para compra de $' . $amountToBuy . '.00 na cidade ' .
            $city . ' quando o dolar chegar a R$' . $requiredPrice;
        ;
    }
}
