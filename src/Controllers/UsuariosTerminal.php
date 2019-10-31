<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\UsuariosTerminal\ModelUsuariosTerminal;

class UsuariosTerminal
{
    static function CreateUserTerminal()
    {
        ActionFilters::Get();
        $obj = new ModelUsuariosTerminal(Request::phpInput());
        Response::status(200)->json($obj->CreateUserTerminal());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelUsuariosTerminal(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function ReadTiposControl()
    {
        ActionFilters::Get();        
        Response::status(200)->json(ModelUsuariosTerminal::ReadTiposControl());
    }

    static function DeleteTerminal()
    {
        ActionFilters::Get();
        $obj = new ModelUsuariosTerminal(Request::phpInput());
        Response::status(200)->json($obj->DeleteTerminal());
    }

    static function ReadByEdit()
    {
        ActionFilters::Get();
        $obj = new ModelUsuariosTerminal(Request::phpInput());
        Response::status(200)->json($obj->ReadByEdit());
    }

    static function UpdateTerminal()
    {
        ActionFilters::Get();
        $obj = new ModelUsuariosTerminal(Request::phpInput());
        Response::status(200)->json($obj->UpdateTerminal());
    }
}