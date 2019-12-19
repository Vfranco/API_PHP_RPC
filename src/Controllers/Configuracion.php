<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Configuracion\ModelConfiguracion;
use Models\Actividades\ModelActividades;

class Configuracion
{
    static function CreateEps()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->CreateEps());
    }

    static function ReadEps()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->ReadEps());
    }

    static function UpdateEps()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->UpdateEps());
    }

    static function DeleteEps()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->DeleteEps());
    }

    static function CreateArl()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->CreateArl());
    }

    static function ReadArl()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->ReadArl());
    }

    static function UpdateArl()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->UpdateArl());
    }

    static function DeleteArl()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->DeleteArl());
    }

    static function CreatePersonalControl()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->CreatePersonalControl());
    }

    static function ReadPersonalControl()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->ReadPersonalControl());
    }

    static function ReadPersonalById()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->ReadPersonalById());
    }

    static function ReadCargos()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->ReadCargos());
    }

    static function CreateCargo()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->CreateCargo());
    }

    static function DeleteCargo()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->DeleteCargo());
    }

    static function UpdateCargo()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->UpdateCargo());
    }

    static function DeletePersonalControl()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->DeletePersonalControl());
    }

    static function UpdatePersonalControl()
    {
        ActionFilters::Get();

        $obj = new ModelConfiguracion(Request::phpInput());
        Response::status(200)->json($obj->UpdatePersonalControl());
    }

    static function CreateActividad()
    {
        ActionFilters::Get();

        $obj = new ModelActividades(Request::phpInput());
        Response::status(200)->json($obj->CreateActividad());
    }

    static function ReadActividades()
    {
        ActionFilters::Get();

        $obj = new ModelActividades(Request::phpInput());
        Response::status(200)->json($obj->ReadActividades());
    }

    static function UpdateActividad()
    {
        ActionFilters::Get();

        $obj = new ModelActividades(Request::phpInput());
        Response::status(200)->json($obj->UpdateActividad());
    }

    static function DeleteActividad()
    {
        ActionFilters::Get();

        $obj = new ModelActividades(Request::phpInput());
        Response::status(200)->json($obj->DeleteActividad());
    }
}