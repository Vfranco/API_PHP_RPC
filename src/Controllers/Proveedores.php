<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Proveedores\ModelProveedores;

class Proveedores
{
    static function CreateContratista()
    {
        ActionFilters::Get();
        $obj = new ModelProveedores(Request::phpInput());
        Response::status(200)->json($obj->CreateContratista());
    }

    static function CreateEmpresa()
    {
        ActionFilters::Get();
        $obj = new ModelProveedores(Request::phpInput());
        Response::status(200)->json($obj->CreateEmpresa());
    }

    static function Read()
    {
        ActionFilters::Get();
        $obj = new ModelProveedores(Request::phpInput());
        Response::status(200)->json($obj->Read());
    }

    static function ReadEmpresas()
    {
        ActionFilters::Get();
        $obj = new ModelProveedores(Request::phpInput());
        Response::status(200)->json($obj->ReadEmpresas());
    }

    static function ReadById()
    {
        ActionFilters::Get();
        $obj = new ModelProveedores(Request::phpInput());
        Response::status(200)->json($obj->ReadById());
    }

    static function ReadByNit()
    {
        ActionFilters::Get();
        $obj = new ModelProveedores(Request::phpInput());
        Response::status(200)->json($obj->ReadByNit());
    }

    static function ReadByContratista()
    {
        ActionFilters::Get();
        $obj = new ModelProveedores(Request::phpInput());
        Response::status(200)->json($obj->ReadByContratista());
    }

    static function Update()
    {
        ActionFilters::Get();
        $obj = new ModelProveedores(Request::phpInput());
        Response::status(200)->json($obj->Update());
    }

    static function UpdateEmpresa()
    {
        ActionFilters::Get();
        $obj = new ModelProveedores(Request::phpInput());
        Response::status(200)->json($obj->UpdateEmpresa());
    }

    static function DisableEmpresa()
    {
        ActionFilters::Get();
        $obj = new ModelProveedores(Request::phpInput());
        Response::status(200)->json($obj->DisableEmpresa());
    }

    static function DeleteContratista()
    {
        ActionFilters::Get();
        $obj = new ModelProveedores(Request::phpInput());
        Response::status(200)->json($obj->DeleteContratista());
    }

    static function UpdateContratista()
    {
        ActionFilters::Get();
        $obj = new ModelProveedores(Request::phpInput());
        Response::status(200)->json($obj->UpdateContratista());
    }
}