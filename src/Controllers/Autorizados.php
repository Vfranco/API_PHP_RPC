<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Autorizados\ModelAutorizados;

class Autorizados
{
    static function Create()
    {
        ActionFilters::Get();
        $obj = new ModelAutorizados(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }

    static function Read()
    {
        ActionFilters::Get();
        Response::status(200)->json(ModelAutorizados::Read());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelAutorizados(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function ReadByCedula()
    {
        ActionFilters::Get();
        $obj = new ModelAutorizados(Request::phpInput());
        Response::status(200)->json($obj->ReadByCedula());
    }

    static function Update()
    {
        ActionFilters::Get();
        $obj = new ModelAutorizados(Request::phpInput());
        Response::status(200)->json($obj->Update());
    }

    static function Delete()
    {
        ActionFilters::Get();
        $obj = new ModelAutorizados(Request::phpInput());
        Response::status(200)->json($obj->Delete());
    }   
}