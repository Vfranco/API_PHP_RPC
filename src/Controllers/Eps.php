<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Eps\ModelEps;

class Eps
{
    static function Create()
    {
        ActionFilters::Get();
        $obj = new ModelEps(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }

    static function Read()
    {
        ActionFilters::Get();
        Response::status(200)->json(ModelEps::Read());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelEps(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function ReadByAll()
    {
        ActionFilters::Get();
        $obj = new ModelEps(Request::phpInput());
        Response::status(200)->json($obj->ReadByAll());
    }

    static function Update()
    {
        ActionFilters::Get();
        $obj = new ModelEps(Request::phpInput());
        Response::status(200)->json($obj->Update());
    }

    static function Delete()
    {
        ActionFilters::Get();
        $obj = new ModelEps(Request::phpInput());
        Response::status(200)->json($obj->Delete());
    }   
}