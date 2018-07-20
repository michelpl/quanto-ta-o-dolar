<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PriceRulesController extends Controller
{

    public function getLowerPrice($originalPrice, int $amountToBuy)
    {
        if ($amountToBuy >= 10000) {
            return $originalPrice - 0.05;
        }

        if ($amountToBuy >= 2000) {
            return $originalPrice - 0.04;
        }

        if ($amountToBuy >= 900) {
            return $originalPrice - 0.03;
        }

        if ($amountToBuy >= 400) {
            return $originalPrice - 0.02;
        }

        return $originalPrice;
    }
}
