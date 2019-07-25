<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Roles\ModelRoles;

class Roles
{
    static function Create()
    {
        ActionFilters::Get();
        $obj = new ModelRoles(Request::phpInput());
        Response::status(200)->json($obj->Create());
    }

    static function Read()
    {
        ActionFilters::Get();
        Response::status(200)->json(ModelRoles::Read());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelRoles(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function Update()
    {
        ActionFilters::Get();
        $obj = new ModelRoles(Request::phpInput());
        Response::status(200)->json($obj->Update());
    }

    static function Delete()
    {
        ActionFilters::Get();
        $obj = new ModelRoles(Request::phpInput());
        Response::status(200)->json($obj->Delete());
    }
}