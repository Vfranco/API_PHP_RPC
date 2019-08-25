<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Registros\ModelRegistros;

class Registros
{
    static function CheckPersonal()
    {
        ActionFilters::Get();
        $registros = new ModelRegistros(Request::phpInput());
        Response::status(200)->json($registros->CheckRegistroPersonal());
    }

    static function Actividad()
    {
        ActionFilters::Get();
        $registros = new ModelRegistros(Request::phpInput());
        Response::status(200)->json($registros->RegistroActividad());
    }

    static function ObtenerVisitas()
    {
        ActionFilters::Get();
        $registros = new ModelRegistros(Request::phpInput());
        Response::status(200)->json($registros->ObtenerResumenVisitas());
    }

    static function ObtenerReporteActividades()
    {
        ActionFilters::Get();
        $registros = new ModelRegistros(Request::phpInput());
        Response::status(200)->json($registros->ObtenerReporteActividades());
    }

    static function ExportExcel()
    {
        ActionFilters::Get();
        $registros = new ModelRegistros(Request::phpInput());
        Response::status(200)->json($registros->ExportExcel());
    }
}