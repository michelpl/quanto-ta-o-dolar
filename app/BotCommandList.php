<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BotCommandList extends Model
{
    public static $HELP =
        "
            Digite **Criar alerta de preço** para R$(preço em reais)' 
            para criar um alerta com o preço que você busca\n
            Digite **Quanto tá o dólar?** para verificar o preço do dólar no 
            momento
        "
    ;
    public static $COMMANDS =[
        'deletePriceAlert' => 'Excluir alerta de preço',
        'createPriceAlert' => 'Criar alerta de preço',
        'howMuchIsDolar' => 'Quanto tá o dólar?'
    ];

    public static $COMMAND_NOT_FOUND =
        "Comando não encontrado \n 
        Envie **Help** para ver a lista com  os comandos";
}
