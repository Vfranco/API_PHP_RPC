<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Reportes\ModelReportes;

class Reportes
{
    static function ReportePorSedes()
    {
        ActionFilters::Get();
        $obj = new ModelReportes(Request::phpInput());
        Response::status(200)->json($obj->ReportePorSedes());
    }    
}