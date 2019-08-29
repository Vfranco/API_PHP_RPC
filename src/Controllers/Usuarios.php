<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Usuarios\ModelUsuarios;

class Usuarios
{
    static function Create()
    {
        ActionFilters::Get();

        $obj = new ModelUsuarios(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }

    static function CheckEmail()
    {
        ActionFilters::Get();

        $obj = new ModelUsuarios(Request::phpInput());
        Response::status(200)->json($obj->CheckEmail());
    }

    static function UpdateTipoRegistro()
    {
        ActionFilters::Get();

        $obj = new ModelUsuarios(Request::phpInput());
        Response::status(200)->json($obj->UpdateTipoRegistro());
    }

    static function UpdateTipoControl()
    {
        ActionFilters::Get();

        $obj = new ModelUsuarios(Request::phpInput());
        Response::status(200)->json($obj->UpdateTipoControl());
    }
}