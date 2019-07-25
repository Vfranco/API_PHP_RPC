<?php

namespace Models\General;

use Database\Database;
use Core\{Validate, Token};

class ModelGeneral
{
    public static function getIdEmpresaByUser($user)
    {
        $getIdUser = self::getIdUserByDecode($user);

        $getId = Database::query([
            'fields'    => "id_cms_empresas",
            'table'     => "cms_empresas",
            'arguments' => "id_acl_user_empresa_fk = '". $getIdUser ."'"
        ])->records()->resultToArray();

        if(isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_cms_empresas'];

    }

    public static function getIdUserByDecode($user)
    {
        $decodeUser = base64_decode($user);

        $getId = Database::query([
            'fields'    => "id_acl_user",
            'table'     => "cms_acl_user",
            'arguments' => "email_acl_user = '". Database::escapeSql($decodeUser) ."'"
        ])->records()->resultToArray();

        if(isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_acl_user'];
    }

    public function getIdUser($id)
    {
        $getId = Database::query([
            'fields'    => "id_acl_user",
            'table'     => "cms_acl_user",
            'arguments' => "id_acl_user = '". Database::escapeSql($id) ."'"
        ])->records()->resultToArray();

        if(isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_acl_user'];
    }

    public function getIdZonaBySede($id)
    {
        $getId = Database::query([
            'fields'    => "id_cms_sede",
            'table'     => "cms_sedes",
            'arguments' => "id_cms_sede = '". Database::escapeSql($id) ."'"
        ])->records()->resultToArray();

        if(isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_cms_sede'];
    }

    public function getIdUserByEmail($email)
    {
        $getId = Database::query([
            'fields'    => "id_acl_user",
            'table'     => "cms_acl_user",
            'arguments' => "email_acl_user = '". Database::escapeSql($email) ."'"
        ])->records()->resultToArray();

        if(isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_acl_user'];
    }

    public function getIdActividadById($id)
    {
        if($id == 1000)
            return 7;

        $getId = Database::query([
            'fields'    => "id_tipo_actividad",
            'table'     => "tipo_actividades",
            'arguments' => "id_tipo_actividad = '". Database::escapeSql($id) ."'"
        ])->records()->resultToArray();

        if(isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_tipo_actividad'];
    }

    public function getFormIdEmpresa($empresa)
    {
        $getForm = Database::query([
            'fields'    => "cms_tipo_formularios_id_cms_tipo_formulario as tipo, form_settings as formSettings",
            'table'     => "cms_sigga_forms",
            'arguments' => "cms_empresas_id_cms_empresas = '". Database::escapeSql($empresa) ."'"            
        ])->records()->resultToArray();

        if(isset($getForm[0]['empty']) && $getForm[0]['empty'] == true)
            return [];

        return [
            'tipo'          => (int) $getForm[0]['tipo'],
            'formSettings'  => $getForm[0]['formSettings']
        ];
    }

    public function getFormIdEmpresaSede($empresa)
    {
        $getForm = Database::query([
            'fields'    => "cf.cms_tipo_formularios_id_cms_tipo_formulario as tipo, replace(ctf.nombre_tipo_formulario, ' ', '_') as nombreFormulario, cf.form_settings as formSettings, cs.nombre_sede as nombreSede, cf.cms_estados_id_cms_estados as estado, ctf.cms_comportamiento_id_cms_comportamiento as comportamiento",
            'table'     => "cms_sigga_forms cf JOIN cms_sedes cs ON cf.cms_sedes_id_cms_sede = cs.id_cms_sede JOIN cms_tipo_formularios ctf ON ctf.id_cms_tipo_formulario = cf.cms_tipo_formularios_id_cms_tipo_formulario",
            'arguments' => "cf.cms_empresas_id_cms_empresas = '". Database::escapeSql($empresa) ."'"            
        ])->records()->resultToArray();

        if(isset($getForm[0]['empty']) && $getForm[0]['empty'] == true)
            return [];

        $result = [];

        foreach($getForm as $key => $value)
        {
            $result[] = [
                'tipo'              => (int) $getForm[$key]['tipo'],
                'formSettings'      => json_decode($getForm[$key]['formSettings']),
                'nombreSede'        => $getForm[$key]['nombreSede'],
                'estado'            => ($getForm[$key]['estado'] == '1') ? true : false,
                'comportamiento'    => (int) $getForm[$key]['comportamiento']
            ];
        }
        
        return $result;
    }

    public function getPersonalRegistrado($empresa)
    {
        $getPersonal = Database::query([
            'fields'    => "cedula_registro as cedula, CONCAT(nombres_registro, ' ', apellidos_registros) as fullName, cms_estados_id_cms_estados as estado",
            'table'     => "cms_registro_personal",
            'arguments' => "cms_empresa_id_cms_empresa = '". Database::escapeSql($empresa) ."' AND cms_estados_id_cms_estados = 1"
        ])->records()->resultToArray();

        if(isset($getPersonal[0]['empty']) && $getPersonal[0]['empty'] == true)
            return [];

        $result = [];

        foreach($getPersonal as $key => $value)
        {
            $result[] = [
                'cedula'    => (int) $getPersonal[$key]['cedula'],
                'fullName'  => $getPersonal[$key]['fullName'],
                'estado'    => (int) $getPersonal[$key]['estado']
            ];
        }
        
        return $result;
    }

    public function getActividades($empresa)
    {
        $getActividad = Database::query([
            'fields'    => "id_tipo_actividad as idTipo, nombre_actividad as nombreActividad",
            'table'     => "tipo_actividades",
            'arguments' => "cms_empresas_id_cms_empresas = '". Database::escapeSql($empresa) ."' AND cms_estados_id_cms_estados = 1"
        ])->records()->resultToArray();

        if(isset($getActividad[0]['empty']) && $getActividad[0]['empty'] == true)
            return [];

        $result = [];

        foreach($getActividad as $key => $value)
        {
            if($getActividad[$key]['nombreActividad'] === _OTRA_ACTIVIDAD)
            {
                $result[] = [
                    'idTipo'            => 1000,
                    'nombreActividad'   => $getActividad[$key]['nombreActividad']
                ];
            }
            else
            {
                $result[] = [
                    'idTipo'            => (int) $getActividad[$key]['idTipo'],
                    'nombreActividad'   => $getActividad[$key]['nombreActividad']
                ];    
            }
        }

        return $result;
    }

    public function uploadImage($image)
    {
        $nameFile = Validate::randomKey(6) . '.jpg';
        file_put_contents(dirname(APP_PATH) . '/Content/'.$nameFile, base64_decode($image));

        return $nameFile;
    }

    public function getPersonalById($id)
    {
        $getId = Database::query([
            'fields'    => "id_registro_personal",
            'table'     => "cms_registro_personal",
            'arguments' => "cedula_registro = '". Database::escapeSql($id) ."'"
        ])->records()->resultToArray();

        if(isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_registro_personal'];
    }

    public function getCedulaByIdEmpleado($id)
    {
        $getId = Database::query([
            'fields'    => "cedula_empleado",
            'table'     => "cms_empleados",
            'arguments' => "id_cms_empleado = '". Database::escapeSql($id) ."'"
        ])->records()->resultToArray();

        if(isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['cedula_empleado'];
    }

    public function checkIfTokenExist($token)
    {
        $getToken = Database::query([
            'fields'    => "id_cms_registro_actividad",
            'table'     => "cms_registro_actividad",
            'arguments' => "token = '". Database::escapeSql($token) ."' LIMIT 1"
        ])->records()->resultToArray();

        if(isset($getToken[0]['empty']) && $getToken[0]['empty'] == true)
            return false;

        return true;
    }

    public function getEquiposList()
    {
        $getEquipos = Database::query([
            'fields'    => "id_cms_tipo_equipo, nombre_tipo_equipo, cms_estados_id_cms_estados as estado",
            'table'     => "cms_tipos_equipos",            
        ])->records()->resultToArray();

        if(isset($getEquipos[0]['empty']) && $getEquipos[0]['empty'] == true)
            return [];

        $result = [];

        foreach($getEquipos as $key => $value)
        {
            if($getEquipos[$key]['estado'] != _ID_ESTADO_INACTIVO)
            {
                if($getEquipos[$key]['nombre_tipo_equipo'] === _OTRA_ACTIVIDAD)
                {
                    $result[] = [
                        'idEquipo'        => 1000,
                        'nombreEquipo'    => $getEquipos[$key]['nombre_tipo_equipo']
                    ];   
                }
                else
                {
                    $result[] = [
                        'idEquipo'        => (int) $getEquipos[$key]['id_cms_tipo_equipo'],
                        'nombreEquipo'    => $getEquipos[$key]['nombre_tipo_equipo']
                    ];
                }                
            }            
        }

        return $result;
    }

    public function getArlList()
    {
        $getArl = Database::query([
            'fields'    => "id_cms_arl, nombre_arl, cms_estados_id_cms_estados as estado",
            'table'     => "cms_arl",            
        ])->records()->resultToArray();

        if(isset($getArl[0]['empty']) && $getArl[0]['empty'] == true)
            return [];

        $result = [];

        foreach($getArl as $key => $value)
        {
            if($getArl[$key]['estado'] != _ID_ESTADO_INACTIVO)
            {
                if($getArl[$key]['nombre_arl'] === _OTRA_ACTIVIDAD)
                {
                    $result[] = [
                        'idArl'        => 1000,
                        'nombreArl'    => $getArl[$key]['nombre_arl']
                    ];   
                }
                else
                {
                    $result[] = [
                        'idArl'        => (int) $getArl[$key]['id_cms_arl'],
                        'nombreArl'    => $getArl[$key]['nombre_arl']
                    ];
                }
            }            
        }

        return $result;
    }

    public function getEpsList()
    {
        $getEps = Database::query([
            'fields'    => "id_cms_eps, nombre_eps, cms_estados_id_cms_estados as estado",
            'table'     => "cms_eps",
        ])->records()->resultToArray();

        if(isset($getEps[0]['empty']) && $getEps[0]['empty'] == true)
            return [];

        $result = [];

        foreach($getEps as $key => $value)
        {
            if($getEps[$key]['estado'] != _ID_ESTADO_INACTIVO)
            {
                if($getEps[$key]['nombre_eps'] === _OTRA_ACTIVIDAD)
                {
                    $result[] = [
                        'idEps'        => 1000,
                        'nombreEps'    => $getEps[$key]['nombre_eps']
                    ];
                }
                else
                {
                    $result[] = [
                        'idEps'        => (int) $getEps[$key]['id_cms_eps'],
                        'nombreEps'    => $getEps[$key]['nombre_eps']
                    ];
                }
                
            }            
        }

        return $result;
    }

    public function getAutorizadoList()
    {
        $getData = Database::query([
            'fields'    => "id_cms_autorizados, cedula_autorizado, CONCAT(nombre_autorizado, ' ', apellidos_autorizado) as autorizado, cms_estados_id_cms_estado as estado",
            'table'     => "cms_autorizados",
        ])->records()->resultToArray();

        if(isset($getData[0]['empty']) && $getData[0]['empty'] == true)
            return [];

        $result = [];

        foreach($getData as $key => $value)
        {
            if($getData[$key]['estado'] != _ID_ESTADO_INACTIVO)
            {
                $result[] = [
                    'idAutorizado'        => (int) $getData[$key]['id_cms_autorizados'],
                    'nombreAutorizado'    => $getData[$key]['autorizado'],
                    'cedulaAutorizado'    => (int) $getData[$key]['cedula_autorizado']
                ];
            }            
        }

        return $result;
    }
    
    public static function recordExist($args)
    {        
        $record = Database::query($args)->records()->resultToArray();

        if(isset($record[0]['empty']) && $record[0]['empty'])
            return false;

        return true;
    }
}
