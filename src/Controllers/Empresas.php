<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Empresas\ModelEmpresas;

class Empresas
{
    static function Create()
    {
        ActionFilters::Get();
        $obj = new ModelEmpresas(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }

    static function Read()
    {
        ActionFilters::Get();
        Response::status(200)->json(ModelEmpresas::Read());
    }

    static function ReadByUser()
    {
        ActionFilters::Get();
        $obj = new ModelEmpresas(Request::phpInput());
        Response::status(200)->json($obj->ReadByUser());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelEmpresas(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function ReadByNit()
    {
        ActionFilters::Get();
        $obj = new ModelEmpresas(Request::phpInput());
        Response::status(200)->json($obj->ReadByNit());
    }

    static function ReadByAll()
    {
        ActionFilters::Get();
        $obj = new ModelEmpresas(Request::phpInput());
        Response::status(200)->json($obj->ReadByAll());
    }

    static function Update()
    {
        ActionFilters::Get();
        $obj = new ModelEmpresas(Request::phpInput());
        Response::status(200)->json($obj->Update());
    }

    static function Disable()
    {
        ActionFilters::Get();
        $obj = new ModelEmpresas(Request::phpInput());
        Response::status(200)->json($obj->Disable());
    }
       
    static function GetConfig()
    {
        ActionFilters::Get();
        $empresa = new ModelEmpresas(Request::phpInput());
        Response::status(200)->json($empresa->GetConfigForm());        
    }    
}