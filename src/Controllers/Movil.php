<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Movil\ModelMovil;

class Movil
{
    static function Authentication()
    {
        ActionFilters::Get();
        $movil = new ModelMovil(Request::phpInput());
        Response::status(200)->json($movil->Authentication());
    }

    static function Register()
    {
        ActionFilters::Get();
        $movil = new ModelMovil(Request::phpInput());
        Response::status(200)->json($movil->CreateUserData());
    }
    
    static function CreateAutorizado()
    {
        ActionFilters::Get();
        $movil = new ModelMovil(Request::phpInput());
        Response::status(200)->json($movil->CreateAutorizado());
    }

    static function ReadAutorizados()
    {
        ActionFilters::Get();
        $movil = new ModelMovil(Request::phpInput());
        Response::status(200)->json($movil->ReadAutorizados());
    }

    static function DeleteAutorizado()
    {
        ActionFilters::Get();
        $movil = new ModelMovil(Request::phpInput());
        Response::status(200)->json($movil->DeleteAutorizado());
    }
}