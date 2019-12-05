<?php

namespace Controllers;

use AppLib\Http\{Request, Response};
use Core\ActionFilters;
use Models\Terminal\ModelTerminal;

class Terminal
{
    static function Authentication()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->Authentication());
    }

    static function Logout()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->Logout());
    }

    static function CreatePersonal()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->CreatePersonal());
    }

    static function CreateVisitante()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->CreateVisitante());
    }

    static function CreateContratista()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->CreateContratista());
    }

    static function RegistraActividad()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->RegistraActividad());
    }

    static function CheckMiPersonal()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->CheckMiPersonal());
    }

    static function CheckVisitante()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->checkVisitante());
    }

    static function CheckContratista()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->CheckContratista());
    }

    static function RegistraVisitaResidencial()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->RegistraVisitaResidencial());
    }

    static function RegistraSalidaVisitante()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->RegistraSalidaVisitante());
    }

    static function UploadPhoto()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->UploadPhoto());
    }

    static function UploadPhotoVisitante()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->UploadPhotoVisitante());
    }

    static function Reload()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->Reload());
    }

    static function CreateUserTerminal()
    {
        /*ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->CreateUserTerminal());*/
    }

    static function RegistraActividadContratista()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->RegistraActividadContratista());
    }

    static function UploadPhotoContratista()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->UploadPhotoContratista());
    }
}
