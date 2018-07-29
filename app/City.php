<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public static $CITIES = [
        "sao-paulo" => ["São Paulo"],
        "rio-de-janeiro" => ["Rio de Janeiro"],
        "belo-horizonte" => ["Belo Horizonte"],
        "brasilia" => ["Brasília"],
        "porto-alegre" => ["Porto Alegre"],
        "florianopolis" => ["Florianópolis"],
        "curitiba" => ["Curitiba"],
        "vila-velha" => ["Vila Velha"],
        "salvador" => ["Salvador"],
        "maceio" => ["Maceió"],
        "manaus" => ["Manaus"],
        "recife" => ["Recife"],
        "campo-grande" => ["Campo Grande"],
        "joao-pessoa" => ["João Pessoa"],
    ];
}