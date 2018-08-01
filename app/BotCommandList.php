<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BotCommandList extends Model
{
    public static $HELP =
       "Digite **Criar alerta de preço**" .
       "para criar um alerta com o preço que você busca\n" .
       "Digite **Quanto tá o dólar?** para verificar o preço do dólar no momento"
    ;
    public static $COMMANDS =[
        'deletePriceAlert' => 'excluir alerta de preço',
        'createPriceAlert' => 'criar alerta de preco',
        'howMuchIsDollar' => 'quanto ta o dolar',
        'help' => 'help',
        'start' => 'start',
        'cancel' => 'cancelar'
    ];

    public static $HELP_COMMANDS =[
        'createPriceAlert' => 'Criar alerta de preço',
        'howMuchIsDollar' => 'Quanto tá o dolar?',
        'help' => 'Help',
        'start' => '/start',
        'cancel' => 'Cancelar'
    ];

    public static $COMMAND_NOT_FOUND =
       "Comando não encontrado \n 
        Envie **Help** para ver a lista com  os comandos";
}