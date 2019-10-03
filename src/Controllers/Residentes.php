<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Residente\ModelResidente;

class Residentes
{
    static function Create()
    {
        ActionFilters::Get();
        $obj = new ModelResidente(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }

    static function Read()
    {
        ActionFilters::Get();
        Response::status(200)->json(ModelResidente::Read());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelResidente(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function ReadResidenteDetalles()
    {
        ActionFilters::Get();
        $obj = new ModelResidente(Request::phpInput());
        Response::status(200)->json($obj->ReadResidenteDetalles());
    }

    static function Asigna()
    {
        ActionFilters::Get();
        $obj = new ModelResidente(Request::phpInput());
        Response::status(200)->json($obj->Asigna());
    }

    static function DesAsignar()
    {
        ActionFilters::Get();
        $obj = new ModelResidente(Request::phpInput());
        Response::status(200)->json($obj->DesAsignar());
    }

    static function Update()
    {
        ActionFilters::Get();
        $obj = new ModelResidente(Request::phpInput());
        Response::status(200)->json($obj->Update());
    }

    static function Delete()
    {
        ActionFilters::Get();
        $obj = new ModelResidente(Request::phpInput());
        Response::status(200)->json($obj->Delete());
    }
}