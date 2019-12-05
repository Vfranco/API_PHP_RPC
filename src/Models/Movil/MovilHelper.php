<?php

namespace Models\Movil;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class MovilHelper
{
    static function isResidente($correo)
    {
        return Database::query([
            'fields'    => "*",
            'table'     => "sg_residentes",
            'arguments' => "correo_residente = '" . Database::escapeSql($correo) . "'"
        ])->rows();
    }

    static function hasCreatedAccount($correo)
    {
        return Database::query([
            'fields'    => "*",
            'table'     => "sg_movil_usuarios",
            'arguments' => "correo = '" . Database::escapeSql($correo) . "'"
        ])->rows();
    }

    static function hasAutorizado($cedula, $correo)
    {
        return Database::query([
            'fields'    => "*",
            'table'     => "sg_movil_autorizados",
            'arguments' => "cedula = '" . Database::escapeSql($cedula) . "' AND correo = '". Database::escapeSql($correo) ."'"
        ])->rows();
    }
}
