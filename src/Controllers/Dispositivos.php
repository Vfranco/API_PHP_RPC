<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Dispositivos\ModelDispositivos;

class Dispositivos
{
    static function Create()
    {
        ActionFilters::Get();
        $obj = new ModelDispositivos(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }

    static function Read()
    {
        ActionFilters::Get();
        Response::status(200)->json(ModelDispositivos::Read());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelDispositivos(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function ReadByImei()
    {
        ActionFilters::Get();
        $obj = new ModelDispositivos(Request::phpInput());
        Response::status(200)->json($obj->ReadByImei());
    }

    static function Update()
    {
        ActionFilters::Get();
        $obj = new ModelDispositivos(Request::phpInput());
        Response::status(200)->json($obj->Update());
    }

    static function Delete()
    {
        ActionFilters::Get();
        $obj = new ModelDispositivos(Request::phpInput());
        Response::status(200)->json($obj->Delete());
    }   
}