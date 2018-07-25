<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Alert;
use App\BotUser;
use App\Telegram;

class AlertController extends Controller
{
    protected $alert;

    public function __construct(Alert $alert) {
        $this->alert = $alert;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->alert->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->alert->fill($request->all());
        $this->alert->save();
        return response($this->alert, 201);
    }

    /**
     * @param $alertId
     * @return mixed
     */
    public function show($alertId)
    {
        return $this->alert->find($alertId);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update($alertId, Request $request)
    {
        $this->alert = $this->alert->find($alertId);
        $this->alert->fill($request->all());
        $this->alert->save();
        return response($this->alert, 200);
    }

    /**
     * Check all alerts
     */
    public function trigger()
    {
        $alerts = $this->alert->all()->where('status', 1);
        $cities = $alerts->groupBy('city');
        $alertsToTrigger = [];
        $messageTriggered = [];

        foreach ($cities as $city => $alerts) {
            $alertsToTrigger[] = $this->checkAlert($city, $alerts);
        }

        foreach ($alertsToTrigger as $alertToTrigger) {
            if ($alertsToTrigger) {
                $messageTriggered[] = $this->sendMessage($alertToTrigger);
            }
        }

        return $messageTriggered;
    }

    protected function checkAlert($city, $alerts)
    {
        $priceController = new PriceController();
        $currentprice = $priceController->buyByCity($city)['original_price'];

        foreach ($alerts as $alert) {
            if ($currentprice <= $alert['required_price']) {
                $alert['current_price'] = $currentprice;
                return $alert;
            }
        }

        return false;
    }

    protected function sendMessage($alert)
    {
        if (!empty($alert['user_id'])) {
            $botUser = new BotUser();
            $user = $botUser->find($alert['user_id']);

            $message =
                "Alerta de preço atingido! \n" .
                "Valor requerido R$" .
                $alert['required_price'] . "\n" .
                "Preço atual R$" . $alert['current_price'] . " para " .
                $alert['city']
                ;

            try{
                $telegram = new Telegram();
                $telegram->sendMessage($user['chat_id'], $message);
                return $message;
                //@todo desabilitar alerta se atingir o preço
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }
}
