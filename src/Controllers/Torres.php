<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Torres\ModelTorres;

class Torres
{
    static function Create()
    {
        ActionFilters::Get();
        $obj = new ModelTorres(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }

    static function CreateNoAplica()
    {
        ActionFilters::Get();
        $obj = new ModelTorres(Request::phpInput());
        Response::status(200)->json($obj->CreateNoAplica());
    }

    static function Read()
    {
        ActionFilters::Get();
        Response::status(200)->json(ModelTorres::Read());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelTorres(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function ReadByIdOficina()
    {
        ActionFilters::Get();
        $obj = new ModelTorres(Request::phpInput());
        Response::status(200)->json($obj->ReadByIdOficina());
    }

    static function ReadByName()
    {
        ActionFilters::Get();
        $obj = new ModelTorres(Request::phpInput());
        Response::status(200)->json($obj->ReadByName());
    }

    static function Update()
    {
        ActionFilters::Get();
        $obj = new ModelTorres(Request::phpInput());
        Response::status(200)->json($obj->Update());
    }

    static function Disable()
    {
        ActionFilters::Get();
        $obj = new ModelTorres(Request::phpInput());
        Response::status(200)->json($obj->Disable());
    }   

    static function Delete()
    {
        ActionFilters::Get();
        $obj = new ModelTorres(Request::phpInput());
        Response::status(200)->json($obj->Delete());
    }
}