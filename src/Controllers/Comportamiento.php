<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Comportamiento\ModelComportamiento;

class Comportamiento
{
    static function Create()
    {
        ActionFilters::Get();
        $obj = new ModelComportamiento(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }

    static function Read()
    {
        ActionFilters::Get();
        Response::status(200)->json(ModelComportamiento::Read());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelComportamiento(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function Update()
    {
        ActionFilters::Get();
        $obj = new ModelComportamiento(Request::phpInput());
        Response::status(200)->json($obj->Update());
    }

    static function Delete()
    {
        ActionFilters::Get();
        $obj = new ModelComportamiento(Request::phpInput());
        Response::status(200)->json($obj->Delete());
    }   
}