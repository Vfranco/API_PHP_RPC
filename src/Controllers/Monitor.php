<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Monitor\ModelMonitor;

class Monitor
{
    static function Personal()
    {
        ActionFilters::Get();
        $obj = new ModelMonitor(Request::phpInput());
        Response::status(200)->json($obj->miPersonal());
    }

    static function Visitantes()
    {
        ActionFilters::Get();
        $obj = new ModelMonitor(Request::phpInput());
        Response::status(200)->json($obj->misVisitantes());
    }

    static function Contratistas()
    {
        ActionFilters::Get();
        $obj = new ModelMonitor(Request::phpInput());
        Response::status(200)->json($obj->misContratistas());
    }
}