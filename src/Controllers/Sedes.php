<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Sedes\ModelSedes;

class Sedes
{
    static function Create()
    {
        ActionFilters::Get();
        $obj = new ModelSedes(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }

    static function Read()
    {
        ActionFilters::Get();
        Response::status(200)->json(ModelSedes::Read());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelSedes(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function ReadByIdEmpresa()
    {
        ActionFilters::Get();
        $obj = new ModelSedes(Request::phpInput());
        Response::status(200)->json($obj->ReadByIdEmpresa());
    }

    static function ReadByAll()
    {
        ActionFilters::Get();
        $obj = new ModelSedes(Request::phpInput());
        Response::status(200)->json($obj->ReadByAll());
    }

    static function Update()
    {
        ActionFilters::Get();
        $obj = new ModelSedes(Request::phpInput());
        Response::status(200)->json($obj->Update());
    }

    static function Disable()
    {
        ActionFilters::Get();
        $obj = new ModelSedes(Request::phpInput());
        Response::status(200)->json($obj->Disable());
    }

    static function Delete()
    {
        ActionFilters::Get();
        $obj = new ModelSedes(Request::phpInput());
        Response::status(200)->json($obj->Delete());
    }
}