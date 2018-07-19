<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp;

class MelhorCambio extends Model
{
    private $url = 'https://www.melhorcambio.com/cotacao/%s/dolar-turismo/%s';

    public function getBuyPrice(string $city, float $value)
    {
        $url = sprintf($this->url, 'compra', $city);

        $client = new GuzzleHttp\Client();
        $res = $client->request('GET', $url);
        if ($res->getStatusCode()) {
            return $this->splitHtml($res->getBody());
        }

        return false;
    }

    private function splitHtml($html)
    {
        $input = explode('id="input-valor-pagar"', $html);

        if (!empty($input[1])) {
            $inputValue = explode(' ', $input[1]);

            if (!empty($inputValue[1])) {
                $price = str_replace('value=', '', $inputValue[1]);

                return str_replace(',', '.', $price);
            }
        }

        return false;
    }
}
