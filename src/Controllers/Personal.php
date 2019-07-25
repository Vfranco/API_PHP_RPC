<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Personal\ModelPersonal;

class Personal
{
    static function Create()
    {
        ActionFilters::Get();
        $obj = new ModelPersonal(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }

    static function Read()
    {
        ActionFilters::Get();
        Response::status(200)->json(ModelPersonal::Read());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelPersonal(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function ReadByCedula()
    {
        ActionFilters::Get();
        $obj = new ModelPersonal(Request::phpInput());
        Response::status(200)->json($obj->ReadByCedula());
    }

    static function ReadByAll()
    {
        ActionFilters::Get();
        $obj = new ModelPersonal(Request::phpInput());
        Response::status(200)->json($obj->ReadByAll());
    }

    static function Update()
    {
        ActionFilters::Get();
        $obj = new ModelPersonal(Request::phpInput());
        Response::status(200)->json($obj->Update());
    }

    static function Delete()
    {
        ActionFilters::Get();
        $obj = new ModelPersonal(Request::phpInput());
        Response::status(200)->json($obj->Delete());
    }   
}