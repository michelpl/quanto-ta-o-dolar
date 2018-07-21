<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PriceRulesController extends Controller
{

    public function getLowerPrice(float $originalPrice, int $amountToBuy)
    {
        $lowerPrice = $originalPrice - 0.01;

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
}
