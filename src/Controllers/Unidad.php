<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Unidad\ModelUnidad;

class Unidad
{
    static function Create()
    {
        ActionFilters::Get();

        $obj = new ModelUnidad(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }    
}