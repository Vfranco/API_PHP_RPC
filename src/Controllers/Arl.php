<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Arl\ModelArl;

class Arl
{
    static function Create()
    {
        ActionFilters::Get();
        $empresas = new ModelArl(Request::phpInput());
        Response::status(200)->json($empresas->Create());
    }

    static function Read()
    {
        ActionFilters::Get();
        Response::status(200)->json(ModelArl::Read());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $empresas = new ModelArl(Request::phpInput());
        Response::status(200)->json($empresas->ReadById());
    }

    static function Update()
    {
        ActionFilters::Get();
        $empresas = new ModelArl(Request::phpInput());
        Response::status(200)->json($empresas->Update());
    }

    static function Delete()
    {
        ActionFilters::Get();
        $empresas = new ModelArl(Request::phpInput());
        Response::status(200)->json($empresas->Delete());
    }   
}