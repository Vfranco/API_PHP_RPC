<?php

namespace Models\Authentication;

use Database\Database;
use Core\{Validate, Token};
use Exception;
use Models\General\ModelGeneral;
use Models\Empresas\ModelEmpresas;

class ModelAuthentication extends ModelGeneral
{
    private $formData;
    private $aclUser;
    private $aclPass;

    public function __construct($formData)
    {
        $this->formData = $formData;
        return $this;
    }

    public function doLogin()
    {
        try {
            if (!Validate::notEmptyFields($this->formData))
                return $this->processLogin($this->formData);
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function doLogout()
    {
        $deleteRow = Database::delete([
            'table'     => "cms_log_user_online",
            'arguments' => "cms_acl_user_id_acl_user = '" . Database::escapeSql($this->formData['idAclUser']) . "'"
        ])->deleteRow();

        if ($deleteRow)
            return ['status' => true, 'message' => 'Se ha cerrado la sesion'];
        else
            return ['status' => false, 'message' => 'Ha ocurrido un error'];
    }

    public function cmsLogin()
    {
        try {
            $this->aclUser = $this->formData['userAcl'];
            $this->aclPass = $this->formData['passAcl'];

            $this->checkIfActive($this->aclUser);

            if ($this->isSessionActive($this->getIdUserByEmail($this->aclUser)))
                return ['status' => false, 'message' => 'Tienes un sesion Activa'];
            else {
                $auth = Database::query([
                    'fields'    => "*",
                    'table'     => "sg_usuarios",
                    'arguments' => "correo = '" . Database::escapeSql($this->aclUser) . "' and password = '" . Database::escapeSql($this->aclPass) . "' LIMIT 1"
                ])->records()->resultToArray();

                if (isset($auth[0]['empty']) && $auth[0]['empty'] == true)
                    return ['status' => false, 'message' => _MSGBOX_ERROR_AUTHENTICATION];
                else {
                    return [
                        'status'    => true,
                        'message'   => 'Sesion iniciada'
                    ];
                }
            }
        } catch (Exception $e) {
            error_log(print_r($e->getMessage(), true));
        }
    }

    private function processLogin($formData)
    {
        $this->aclUser = $formData['userAcl'];
        $this->aclPass = $formData['passAcl'];

        $this->checkIfActive($this->aclUser);

        if ($this->isSessionActive($this->getIdUserByEmail($this->aclUser)))
            return ['status' => false, 'message' => 'Tienes un sesion Activa'];
        else {
            $auth = Database::query([
                'fields'    => "cu.id_acl_user as idAclUser, CONCAT(cu.fname_acl_user, ' ', cu.lname_acl_user) as fullname, ce.id_cms_empresas as idCmsEmpresa, ce.nombre_empresa as nombreEmpresa, cs.id_cms_sede as idSede, cs.nombre_sede as nombreSede",
                'table'     => "cms_acl_user cu join cms_empresas ce on cu.cms_empresas_id_cms_empresas = ce.id_cms_empresas join cms_sedes cs on cs.cms_empresas_id_cms_empresas = ce.id_cms_empresas",
                'arguments' => "cu.email_acl_user = '" . Database::escapeSql($this->aclUser) . "' and cu.password_acl_user = '" . Database::escapeSql($this->aclPass) . "' limit 1"
            ])->records()->resultToArray();

            if (isset($auth[0]['empty']) && $auth[0]['empty'] == true)
                return ['status' => false, 'message' => _MSGBOX_ERROR_AUTHENTICATION];
            else {
                $getDataUserLog = $this->saveLog($this->aclUser);

                if (count($getDataUserLog) > 0) {
                    Database::insert([
                        'table'     => 'cms_log_user_online',
                        'values'    => [
                            'cms_dispositivos_id_cms_dispositivo'  => $getDataUserLog['cms_dispositivos_id_cms_dispositivo'],
                            'cms_acl_user_id_acl_user'             => $getDataUserLog['cms_acl_user_id_acl_user'],
                            'cms_estados_id_cms_estados'           => $getDataUserLog['cms_estados_id_cms_estados'],
                            'token_log_user_online'                => $getDataUserLog['token_log_user_online'],
                            'fecha_inicio_sesion'                  => $getDataUserLog['fecha_inicio_sesion']
                        ],
                        'autoinc'   => true
                    ])->affectedRow();
                }

                return [
                    'status'        => true,
                    'objUser'       => [
                        "idAclUser"     => (int) $auth[0]['idAclUser'],
                        "fullName"      => $auth[0]['fullname'],
                        "idCmsEmpresa"  => (int) $auth[0]['idCmsEmpresa'],
                        "nombreEmpresa" => $auth[0]['nombreEmpresa'],
                        "idSede"        => (int) $auth[0]['idSede'],
                        "nombreSede"    => $auth[0]['nombreSede']
                    ],
                    'formularios'   => [
                        'Control_de_Personal'       => $this->getFormIdEmpresaSede($auth[0]['idCmsEmpresa'])[0],
                        'Control_de_Visitas'        => $this->getFormIdEmpresaSede($auth[0]['idCmsEmpresa'])[1],
                        'Control_de_Proveedores'    => $this->getFormIdEmpresaSede($auth[0]['idCmsEmpresa'])[2]

                    ],
                    'personal'      => $this->getPersonalRegistrado($auth[0]['idCmsEmpresa']),
                    'actividades'   => $this->getActividades($auth[0]['idCmsEmpresa']),
                    'empresas'      => ModelEmpresas::getEmpresasList(),
                    'equipos'       => $this->getEquiposList(),
                    'arl'           => $this->getArlList(),
                    'eps'           => $this->getEpsList(),
                    'autorizados'   => $this->getAutorizadoList(),
                    'token'         => Token::isEmpty($getDataUserLog['token_log_user_online'])
                ];
            }
        }
    }

    private function saveLog($user)
    {
        $getLogUser = Database::query([
            'fields'    => "cd.id_cms_dispositivo, cu.id_acl_user, cu.cms_estados_id_cms_estados, cd.random_key, cd.secret_key",
            'table'     => "cms_acl_user cu join cms_empresas ce on ce.id_acl_user_empresa_fk = cu.id_acl_user join cms_dispositivos cd on ce.id_cms_empresas = cd.cms_empresas_id_cms_empresas",
            'arguments' => "cu.email_acl_user = '" . Database::escapeSql($user) . "' LIMIT 1"
        ])->records()->resultToArray();

        if (isset($getLogUser[0]['empty']) && $getLogUser[0]['empty'] == true)
            return [];

        return [
            'cms_dispositivos_id_cms_dispositivo'  => $getLogUser[0]['id_cms_dispositivo'],
            'cms_acl_user_id_acl_user'             => $getLogUser[0]['id_acl_user'],
            'cms_estados_id_cms_estados'           => $getLogUser[0]['cms_estados_id_cms_estados'],
            'token_log_user_online'                => Token::create($getLogUser[0]['secret_key'], ['randomKey' => $getLogUser[0]['random_key']]),
            'fecha_inicio_sesion'                  => Database::dateTime()
        ];
    }

    private function checkIfActive($user)
    {
        $active = Database::query([
            'fields'    => "id_sg_estado",
            'table'     => "sg_usuarios",
            'arguments' => "correo = '" . Database::escapeSql($user) . "' LIMIT 1"
        ])->records()->resultToArray();

        if (isset($active[0]['empty']) && $active[0]['empty'] == true)
            return ['status' => false, 'message' => _MSGBOX_ERROR_AUTHENTICATION];

        $result = [];

        foreach ($active[0] as $key => $value) {
            if ($value != 1) {
                $result = ['status' => false, 'message' => $key . " no se encuentra activo"];
                break;
            }
        }

        if (count($result) <= 0)
            return false;
        else
            exit(json_encode($result, JSON_PRETTY_PRINT));
    }

    private function isSessionActive($id)
    {
        $isActive = Database::query([
            'fields'    => "id_sg_usuario",
            'table'     => "sg_usuarios_en_linea",
            'arguments' => "id_sg_usuario = '" . Database::escapeSql($id) . "'"
        ])->records()->resultToArray();

        if (isset($isActive[0]['empty']) && $isActive[0]['empty'] == true)
            return false;

        if (count($isActive[0]) > 0)
            return true;

        return false;
    }
}
