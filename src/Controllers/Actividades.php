<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Actividades\ModelActividades;

class Actividades
{
    static function Create()
    {
        ActionFilters::Get();
        $obj = new ModelActividades(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }

    static function Read()
    {
        ActionFilters::Get();
        Response::status(200)->json(ModelActividades::Read());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelActividades(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function ReadByAll()
    {
        ActionFilters::Get();
        $obj = new ModelActividades(Request::phpInput());
        Response::status(200)->json($obj->ReadByAll());
    }

    static function Update()
    {
        ActionFilters::Get();
        $obj = new ModelActividades(Request::phpInput());
        Response::status(200)->json($obj->Update());
    }

    static function Disable()
    {
        ActionFilters::Get();
        $obj = new ModelActividades(Request::phpInput());
        Response::status(200)->json($obj->Disable());
    }
}