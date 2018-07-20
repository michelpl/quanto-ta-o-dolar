<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MelhorCambio;
use App\Http\Controllers\API\PriceRulesController;

class PriceController extends Controller
{
    private static $MINIMUM_VALUE_MSG = 'Insira um valor inteiro maior que R$100.00';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  string  $city
     * @param  int $amountToBuy Value to buy (only int > 100 allowed)
     * @return \Illuminate\Http\Response
     */
    public function buyByCity(string $city, int $amountToBuy)
    {
        if ($amountToBuy < 100) {
            return self::$MINIMUM_VALUE_MSG;
        }

        $melhorCambio = new MelhorCambio();
        $originalPrice = $melhorCambio->getBuyPrice($city);

        $priceRules = new PriceRulesController();

        return $priceRules->getLowerPrice($originalPrice, $amountToBuy);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
