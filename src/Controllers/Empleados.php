<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Empleados\ModelEmpleados;

class Empleados
{
    static function Create()
    {
        ActionFilters::Get();
        $obj = new ModelEmpleados(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }

    static function Read()
    {
        ActionFilters::Get();
        Response::status(200)->json(ModelEmpleados::Read());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelEmpleados(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function ReadByEmpresa()
    {
        ActionFilters::Get();
        $obj = new ModelEmpleados(Request::phpInput());
        Response::status(200)->json($obj->ReadByEmpresa());
    }

    static function ReadByCedula()
    {
        ActionFilters::Get();
        $obj = new ModelEmpleados(Request::phpInput());
        Response::status(200)->json($obj->ReadByCedula());
    }

    static function Update()
    {
        ActionFilters::Get();
        $obj = new ModelEmpleados(Request::phpInput());
        Response::status(200)->json($obj->Update());
    }

    static function Disable()
    {
        ActionFilters::Get();
        $obj = new ModelEmpleados(Request::phpInput());
        Response::status(200)->json($obj->Disable());
    }   

    static function Delete()
    {
        ActionFilters::Get();
        $obj = new ModelEmpleados(Request::phpInput());
        Response::status(200)->json($obj->Delete());
    }
}