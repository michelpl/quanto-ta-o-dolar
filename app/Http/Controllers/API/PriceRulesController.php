<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PriceRulesController extends Controller
{

    public function getLowerPrice(float $originalPrice, int $amountToBuy)
    {
        $lowerPrice = $originalPrice;

        if ($amountToBuy >= 10000) {
            $lowerPrice = $originalPrice - 0.05;
        } elseif ($amountToBuy >= 2000) {
            $lowerPrice = $originalPrice - 0.04;
        } elseif ($amountToBuy >= 900) {
            $lowerPrice = $originalPrice - 0.03;
        } elseif ($amountToBuy >= 400) {
            $lowerPrice = $originalPrice - 0.02;
        }

        return number_format($lowerPrice, 2);
    }

    public static $values = [
        'R$100,00' => 100,
        'R$400,00 ou mais' => 400,
        'R$900,00 ou mais' => 900,
        'R$2000,00 ou mais' => 2000,
        'R$10000,00 ou mais' => 10000
    ];
}
