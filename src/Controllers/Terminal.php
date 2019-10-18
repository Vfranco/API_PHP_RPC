<?php

namespace Controllers;

use AppLib\Http\{Request, Response};
use Core\ActionFilters;
use Models\Terminal\ModelTerminal;
use React\Socket\Server;
use React\Http\Response as ReactResponse;
use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;

class Terminal
{
    static function initWebSocket()
    {
        
    }

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

    static function RegistraActividad()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->RegistraActividad());
    }

    static function checkVisitante()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->checkVisitante());
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

    static function Reload()
    {
        ActionFilters::Get();
        $obj = new ModelTerminal(Request::phpInput());
        Response::status(200)->json($obj->Reload());
    }
}
