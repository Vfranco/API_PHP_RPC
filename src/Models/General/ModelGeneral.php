<?php

namespace Models\General;

use Database\Database;
use Core\{Validate, Token};

class ModelGeneral
{
    public function getTipoControlByUser($user)
    {
        $tipo = Database::query([
            'fields'    => "id_sg_tipo_control",
            'table'     => "sg_registros",
            'arguments' => "correo = '" . base64_decode($user) . "'"
        ])->records()->resultToArray();

        if (!self::hasRows($tipo))
            return [];

        return $tipo[0]['id_sg_tipo_control'];
    }

    public static function getTerminalIdByUserName($user)
    {
        $username = Database::query([
            'fields'    => "id_sg_terminal_usuario",
            'table'     => "sg_terminal_usuarios",
            'arguments' => "usuario = '" . $user . "'"
        ])->records()->resultToArray();

        if (!self::hasRows($username))
            return [];

        return $username[0]['id_sg_terminal_usuario'];
    }

    public static function getIdProveedorByUid($uid)
    {
        $getId = Database::query([
            'fields'    => "id_sg_mi_proveedor",
            'table'     => "sg_mis_proveedores",
            'arguments' => "creado_por = '" . $uid . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_sg_mi_proveedor'];
    }

    public static function getIdContratistaByCedula($cedula)
    {
        $getId = Database::query([
            'fields'    => "id_sg_personal_proveedor",
            'table'     => "sg_personal_proveedor",
            'arguments' => "cedula_proveedor = '" . $cedula . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return "";

        return $getId[0]['id_sg_personal_proveedor'];
    }

    public static function getIdEmpresaByUser($user)
    {
        $getIdUser = self::getIdUserByDecode($user);

        $getId = Database::query([
            'fields'    => "id_sg_empresa",
            'table'     => "sg_empresas",
            'arguments' => "id_sg_usuario = '" . $getIdUser . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_sg_empresa'];
    }

    public static function getIdEmpresaByTerminal($idterminal)
    {
        $getId = Database::query([
            'fields'    => "id_sg_empresa",
            'table'     => "sg_terminal_usuarios",
            'arguments' => "id_sg_terminal_usuario = '" . $idterminal . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_sg_empresa'];
    }

    public static function getIdUnidadResidencialByUser($user)
    {
        $getIdUser = self::getIdUserByDecode($user);

        $getId = Database::query([
            'fields'    => "id_sg_unidad_residencial",
            'table'     => "sg_unidad_residencial",
            'arguments' => "id_sg_usuario = '" . $getIdUser . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_sg_unidad_residencial'];
    }

    public static function getIdUserByDecode($user)
    {
        $decodeUser = base64_decode($user);

        $getId = Database::query([
            'fields'    => "id_sg_usuario",
            'table'     => "sg_usuarios",
            'arguments' => "correo = '" . Database::escapeSql($decodeUser) . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_sg_usuario'];
    }

    public static function getCorreoByDecode($user)
    {
        $decodeUser = base64_decode($user);
        return $decodeUser;
    }

    public function getIdUser($id)
    {
        $getId = Database::query([
            'fields'    => "id_sg_usuario",
            'table'     => "sg_usuarios",
            'arguments' => "id_sg_usuario = '" . Database::escapeSql($id) . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_sg_usuario'];
    }

    public function getIdZonaBySede($id)
    {
        $getId = Database::query([
            'fields'    => "id_cms_sede",
            'table'     => "cms_sedes",
            'arguments' => "id_cms_sede = '" . Database::escapeSql($id) . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_cms_sede'];
    }

    public function getIdUserByEmail($email)
    {
        $getId = Database::query([
            'fields'    => "id_sg_usuario",
            'table'     => "sg_usuarios",
            'arguments' => "correo = '" . Database::escapeSql($email) . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_sg_usuario'];
    }

    public function getIdActividadById($id)
    {
        if ($id == 1000)
            return 7;

        $getId = Database::query([
            'fields'    => "id_tipo_actividad",
            'table'     => "tipo_actividades",
            'arguments' => "id_tipo_actividad = '" . Database::escapeSql($id) . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_tipo_actividad'];
    }

    public function getFormIdEmpresa($empresa)
    {
        $getForm = Database::query([
            'fields'    => "cms_tipo_formularios_id_cms_tipo_formulario as tipo, form_settings as formSettings",
            'table'     => "cms_sigga_forms",
            'arguments' => "cms_empresas_id_cms_empresas = '" . Database::escapeSql($empresa) . "'"
        ])->records()->resultToArray();

        if (isset($getForm[0]['empty']) && $getForm[0]['empty'] == true)
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
            'arguments' => "cf.cms_empresas_id_cms_empresas = '" . Database::escapeSql($empresa) . "'"
        ])->records()->resultToArray();

        if (isset($getForm[0]['empty']) && $getForm[0]['empty'] == true)
            return [];

        $result = [];

        foreach ($getForm as $key => $value) {
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
            'arguments' => "cms_empresa_id_cms_empresa = '" . Database::escapeSql($empresa) . "' AND cms_estados_id_cms_estados = 1"
        ])->records()->resultToArray();

        if (isset($getPersonal[0]['empty']) && $getPersonal[0]['empty'] == true)
            return [];

        $result = [];

        foreach ($getPersonal as $key => $value) {
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
            'arguments' => "cms_empresas_id_cms_empresas = '" . Database::escapeSql($empresa) . "' AND cms_estados_id_cms_estados = 1"
        ])->records()->resultToArray();

        if (isset($getActividad[0]['empty']) && $getActividad[0]['empty'] == true)
            return [];

        $result = [];

        foreach ($getActividad as $key => $value)
        {
            if ($getActividad[$key]['nombreActividad'] === _OTRA_ACTIVIDAD)
            {
                $result[] = [
                    'idTipo'            => 1000,
                    'nombreActividad'   => $getActividad[$key]['nombreActividad']
                ];
            } else {
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
        file_put_contents(dirname(APP_PATH) . '/Content/' . $nameFile, base64_decode($image));

        return $nameFile;
    }

    private function cropImage($image, $width, $height)
    {
        header('Content-Type: image/png');

        imagepng($image);
        imagedestroy($image);

        $resource = @imagecreatefrompng($image);

        if(!$resource)
            return false;

        $w = @imagesx($resource);
        $h = @imagesy($resource);

        if ((!$w) || (!$h))
        {
            $GLOBALS['errors'][] = 'Image couldn\'t be resized because it wasn\'t a valid image.';
            return false;
        }

        if (($w == $width) && ($h == $height))
        {
            return $image;
        }

        $ratio = $width / $w;
        $new_w = $width;
        $new_h = $h * $ratio;

        if ($new_h < $height)
        {
            $ratio = $height / $h;
            $new_h = $height;
            $new_w = $w * $ratio;
        }

        $image2 = imagecreatetruecolor($new_w, $new_h);

        imagecopyresampled($image2, $image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);

        if (($new_h != $height) || ($new_w != $width))
        {
            $image3 = imagecreatetruecolor($width, $height);

            if ($new_h > $height) 
            {
                $extra = $new_h - $height;
                $x = 0; //source x
                $y = round($extra / 2); //source y
                imagecopyresampled($image3, $image2, 0, 0, $x, $y, $width, $height, $width, $height);
            } 
            else
            {
                $extra = $new_w - $width;
                $x = round($extra / 2); //source x
                $y = 0; //source y
                imagecopyresampled($image3, $image2, 0, 0, $x, $y, $width, $height, $width, $height);
            }

            imagedestroy($image2);

            return $image3;
        }
        else
            return $image2;
    }

    public function getPersonalById($id)
    {
        $getId = Database::query([
            'fields'    => "id_registro_personal",
            'table'     => "cms_registro_personal",
            'arguments' => "cedula_registro = '" . Database::escapeSql($id) . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_registro_personal'];
    }

    public function getCedulaByIdEmpleado($id)
    {
        $getId = Database::query([
            'fields'    => "cedula_personal",
            'table'     => "sg_mi_personal",
            'arguments' => "id_sg_personal = '" . Database::escapeSql($id) . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['cedula_personal'];
    }

    public static function getIdPersonalByCedula($cedula)
    {
        $getId = Database::query([
            'fields'    => "id_sg_personal",
            'table'     => "sg_mi_personal",
            'arguments' => "cedula_personal = '" . Database::escapeSql($cedula) . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return "";

        return $getId[0]['id_sg_personal'];
    }

    public static function getIdSedeByCedula($cedula)
    {
        $getId = Database::query([
            'fields'    => "id_sg_sede",
            'table'     => "sg_mi_personal",
            'arguments' => "cedula_personal = '" . Database::escapeSql($cedula) . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_sg_sede'];
    }

    public static function getIdSedeByTerminal($terminal)
    {
        $getId = Database::query([
            'fields'    => "id_sg_sede",
            'table'     => "sg_terminales",
            'arguments' => "id_sg_terminal = '" . Database::escapeSql($terminal) . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['id_sg_sede'];
    }

    public static function getNombreSedeById($id)
    {
        $getId = Database::query([
            'fields'    => "nombre_sede",
            'table'     => "sg_sedes",
            'arguments' => "id_sg_sede = '" . Database::escapeSql($id) . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['nombre_sede'];
    }

    public static function getNombresEmpleadoByCedula($cedula)
    {
        $getId = Database::query([
            'fields'    => "concat(nombres_personal, ' ', apellidos_personal) as empleado",
            'table'     => "sg_mi_personal",
            'arguments' => "cedula_personal = '" . Database::escapeSql($cedula) . "'"
        ])->records()->resultToArray();

        if (isset($getId[0]['empty']) && $getId[0]['empty'] == true)
            return [];

        return $getId[0]['empleado'];
    }

    public function checkIfTokenExist($token)
    {
        $getToken = Database::query([
            'fields'    => "id_cms_registro_actividad",
            'table'     => "cms_registro_actividad",
            'arguments' => "token = '" . Database::escapeSql($token) . "' LIMIT 1"
        ])->records()->resultToArray();

        if (isset($getToken[0]['empty']) && $getToken[0]['empty'] == true)
            return false;

        return true;
    }

    public function getEquiposList()
    {
        $getEquipos = Database::query([
            'fields'    => "id_cms_tipo_equipo, nombre_tipo_equipo, cms_estados_id_cms_estados as estado",
            'table'     => "cms_tipos_equipos",
        ])->records()->resultToArray();

        if (isset($getEquipos[0]['empty']) && $getEquipos[0]['empty'] == true)
            return [];

        $result = [];

        foreach ($getEquipos as $key => $value) {
            if ($getEquipos[$key]['estado'] != _ID_ESTADO_INACTIVO) {
                if ($getEquipos[$key]['nombre_tipo_equipo'] === _OTRA_ACTIVIDAD) {
                    $result[] = [
                        'idEquipo'        => 1000,
                        'nombreEquipo'    => $getEquipos[$key]['nombre_tipo_equipo']
                    ];
                } else {
                    $result[] = [
                        'idEquipo'        => (int) $getEquipos[$key]['id_cms_tipo_equipo'],
                        'nombreEquipo'    => $getEquipos[$key]['nombre_tipo_equipo']
                    ];
                }
            }
        }

        return $result;
    }

    public function getArlList($correo)
    {
        $getArl = Database::query([
            'fields'    => "id_sg_arl, nombre_arl, id_sg_estado as estado",
            'table'     => "sg_arl",
            'arguments' => "creado_por = '" . $correo . "'"
        ])->records()->resultToArray();

        if (isset($getArl[0]['empty']) && $getArl[0]['empty'] == true)
            return [];

        $result = [];

        foreach ($getArl as $key => $value) {
            if ($getArl[$key]['estado'] != _ID_ESTADO_INACTIVO) {
                if ($getArl[$key]['nombre_arl'] === _OTRA_ACTIVIDAD) {
                    $result[] = [
                        'idArl'        => 1000,
                        'nombreArl'    => $getArl[$key]['nombre_arl']
                    ];
                } else {
                    $result[] = [
                        'idArl'        => (int) $getArl[$key]['id_sg_arl'],
                        'nombreArl'    => $getArl[$key]['nombre_arl']
                    ];
                }
            }
        }

        return $result;
    }

    public function getEpsList($correo)
    {
        $getEps = Database::query([
            'fields'    => "id_sg_eps, nombre_eps, id_sg_estado as estado",
            'table'     => "sg_eps",
            'arguments' => "creado_por = '" . $correo . "'"
        ])->records()->resultToArray();

        if (isset($getEps[0]['empty']) && $getEps[0]['empty'] == true)
            return [];

        $result = [];

        foreach ($getEps as $key => $value) {
            if ($getEps[$key]['estado'] != _ID_ESTADO_INACTIVO) {
                if ($getEps[$key]['nombre_eps'] === _OTRA_ACTIVIDAD) {
                    $result[] = [
                        'idEps'        => 1000,
                        'nombreEps'    => $getEps[$key]['nombre_eps']
                    ];
                } else {
                    $result[] = [
                        'idEps'        => (int) $getEps[$key]['id_sg_eps'],
                        'nombreEps'    => $getEps[$key]['nombre_eps']
                    ];
                }
            }
        }

        return $result;
    }

    public function getMotivoList($correo)
    {
        $getEps = Database::query([
            'fields'    => "id_sg_motivo, nombre_motivo, id_sg_estado as estado",
            'table'     => "sg_motivos",
            'arguments' => "creado_por = '" . $correo . "'"
        ])->records()->resultToArray();

        if (isset($getEps[0]['empty']) && $getEps[0]['empty'] == true)
            return [];

        $result = [];

        foreach ($getEps as $key => $value) {
            if ($getEps[$key]['estado'] != _ID_ESTADO_INACTIVO) {
                if ($getEps[$key]['nombre_motivo'] === _OTRA_ACTIVIDAD) {
                    $result[] = [
                        'idMotivo'        => 1000,
                        'nombreMotivo'    => $getEps[$key]['nombre_motivo']
                    ];
                } else {
                    $result[] = [
                        'idMotivo'        => (int) $getEps[$key]['id_sg_motivo'],
                        'nombreMotivo'    => $getEps[$key]['nombre_motivo']
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

        if (isset($getData[0]['empty']) && $getData[0]['empty'] == true)
            return [];

        $result = [];

        foreach ($getData as $key => $value) {
            if ($getData[$key]['estado'] != _ID_ESTADO_INACTIVO) {
                $result[] = [
                    'idAutorizado'        => (int) $getData[$key]['id_cms_autorizados'],
                    'nombreAutorizado'    => $getData[$key]['autorizado'],
                    'cedulaAutorizado'    => (int) $getData[$key]['cedula_autorizado']
                ];
            }
        }

        return $result;
    }

    public function getActividadesList($uid)
    {
        $getEps = Database::query([
            'fields'    => "id_sg_tipo_de_actividad, nombre_actividad, id_sg_estado as estado",
            'table'     => "sg_tipos_de_actividad",
            'arguments' => "creado_por = '". $uid ."'"
        ])->records()->resultToArray();

        if (isset($getEps[0]['empty']) && $getEps[0]['empty'] == true)
            return [];

        $result = [];

        foreach ($getEps as $key => $value) {
            if ($getEps[$key]['estado'] != _ID_ESTADO_INACTIVO) {
                if ($getEps[$key]['nombre_actividad'] === _OTRA_ACTIVIDAD) {
                    $result[] = [
                        'idActividad'       => 1000,
                        'nombreActividad'   => $getEps[$key]['nombre_actividad']
                    ];
                } else {
                    $result[] = [
                        'idActividad'       => (int) $getEps[$key]['id_sg_tipo_de_actividad'],
                        'nombreActividad'   => $getEps[$key]['nombre_actividad']
                    ];
                }
            }
        }

        return $result;
    }

    public function getEmpresasList($uid)
    {
        $getEmpresa = Database::query([
            'fields'    => "id_sg_mi_proveedor, nombre_proveedor, id_sg_estado as estado",
            'table'     => "sg_mis_proveedores",
            'arguments' => "creado_por = '" . $uid . "'"
        ])->records()->resultToArray();

        if (isset($getEmpresa[0]['empty']) && $getEmpresa[0]['empty'] == true)
            return [];

        $result = [];

        foreach ($getEmpresa as $key => $value) {
            if ($getEmpresa[$key]['estado'] != _ID_ESTADO_INACTIVO) {
                if ($getEmpresa[$key]['nombre_proveedor'] === _OTRA_ACTIVIDAD) {
                    $result[] = [
                        'idEmpresa'       => 1000,
                        'nombreEmpresa'   => $getEmpresa[$key]['nombre_proveedor']
                    ];
                } else {
                    $result[] = [
                        'idEmpresa'       => (int) $getEmpresa[$key]['id_sg_mi_proveedor'],
                        'nombreEmpresa'   => $getEmpresa[$key]['nombre_proveedor']
                    ];
                }
            }
        }

        return $result;
    }

    public static function recordExist($args)
    {
        $record = Database::query($args)->records()->resultToArray();

        if (isset($record[0]['empty']) && $record[0]['empty'] == true)
            return false;

        return true;
    }

    public static function getIdVisitanteByCedula($cedula)
    {
        $getData = Database::query([
            'fields'    => "id_sg_visitante",
            'table'     => "sg_mis_visitantes",
            'arguments' => "cedula = '" . $cedula . "'"
        ])->records()->resultToArray();

        if (isset($getData[0]['empty']) && $getData[0]['empty'] == true)
            return "";

        return $getData[0]['id_sg_visitante'];
    }

    public static function hasRows($resource)
    {
        if (isset($resource[0]['empty']) && $resource[0]['empty'] == true)
            return false;

        return true;
    }

    public static function setTipoRegistro($tipoRegistro)
    {
        $tipo = '';

        if (!isset($tipoRegistro) || empty($tipoRegistro))
            return 'no set tipo';
        else {
            switch ($tipoRegistro) {
                case 1:
                    $tipo = 'personal';
                    break;

                case 2:
                    $tipo = 'visitantes';
                    break;

                case 3:
                    $tipo = 'contratistas';
                    break;
            }
        }

        return $tipo;
    }

    public static function changeFormatDate($date)
    {
        return $date;
    }

    public static function removeWhiteSpaces($nombres)
    {
        if(preg_match_all('/(\w+)/', $nombres, $output))
            $result = $output;

        $dataContratista = $result[0];
        return $dataContratista[0] . " " . $dataContratista[1] . " " . $dataContratista[2] . " " . $dataContratista[3];
    }
}
