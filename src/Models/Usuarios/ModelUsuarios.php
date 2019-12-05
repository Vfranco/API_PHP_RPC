<?php

namespace Models\Usuarios;

use Database\Database;
use Core\{Validate, Helper};
use Models\General\ModelGeneral;

class ModelUsuarios
{
    private $formData;
    
    public function __construct($formData)
    {
        $this->formData = $formData;
        return $this;
    }

    public function Create()
    {
        try {

            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                $usuarioExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_registros",
                    'arguments'  => "correo = '". $this->formData['correo'] ."'"
                ]);

                if($usuarioExist)
                    return ['status' => false, 'message' => "Ya tienes una cuenta, puedes iniciar sesión"];

                $saveRegistro = Database::insert([
                    'table'     => 'sg_registros',
                    'values'    => [                                                
                        "id_sg_plan"                    => $this->formData['plan'],
                        "id_sg_tipo_control"            => 3,
                        "nombres"                       => $this->formData['nombres'],
                        "apellidos"                     => $this->formData['apellidos'],
                        "correo"                        => $this->formData['correo'],
                        "acepta_acuerdos"               => 1,
                        "fecha_registro"                => Database::dateTime()
                    ],                    
                    'autoinc'   => true                    
                ])->affectedRow();

                $getId = Database::query([
                    'fields'    => "id_sg_registro",
                    'table'     => "sg_registros",
                    'arguments' => "correo = '". $this->formData['correo'] ."' ORDER by id_sg_registro DESC"
                ])->assoc('id_sg_registro');

                $saveUsuario = Database::insert([
                    'table'     => 'sg_usuarios',
                    'values'    => [
                        'id_sg_registro'        => $getId,
                        'id_sg_tipo_registro'   => 1,
                        'id_sg_estado'          => 1,                        
                        'correo'                => $this->formData['correo'],
                        'password'              => $this->formData['pass'],
                        'intentos'              => 1,
                        'bienvenida'            => 0,
                        'fecha_expiracion'      => Helper::setExpirationDate(Database::date(), Helper::getDaysFromPlan($this->formData['plan'])),
                        'endpoint'              => '#!/empresas'
                    ],
                    'autoinc'   => true
                ])->affectedRow();

                $getIdUser = Database::query([
                    'fields'    => "id_sg_usuario",
                    'table'     => "sg_usuarios",
                    'arguments' => "id_sg_registro = '". $getId ."' ORDER by id_sg_registro DESC"
                ])->assoc('id_sg_usuario');

                $inc = 1;

                foreach(MENU_DEFAULT as $key => $value)
                {
                    $saveMenu = Database::insert(
                    [
                        'table'     => "sg_menu_usuarios",
                        'values'    => [
                            "id_sg_usuario" => $getIdUser,
                            "id_sg_estado"  => $value['estado'],
                            "nombre_menu"   => $key,
                            "href_menu"     => $value['href'],
                            "icon_menu"     => $value['icon'],
                            "color_menu"    => $value['color'],
                            "posicion_menu" => $inc
                        ],
                        'autoinc'   => true                        
                    ])->affectedRow();

                    $inc++;
                }

                if($saveRegistro && $saveUsuario && $saveMenu)
                    return ['status' => true, 'message' => 'Usuario Registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el Usuario'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function CheckEmail()
    {
        if(Validate::notEmptyFields($this->formData))
            return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
        else
        {
            $emailExist = ModelGeneral::recordExist([
                'fields'     => "*",
                'table'      => "sg_registros",
                'arguments'  => "correo = '". $this->formData['correo'] ."'"
            ]);

            if($emailExist)
                return ['status' => true, 'message' => 'Ya existe una cuenta, con este correo'];
            else
                return ['status' => false, 'message' => 'Bien!, hemos validado tu correo'];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_acl_user",
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros de Autorizados'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_acl_user",
            'arguments' => "id_cms_acl_user = '". $this->formData['id_cms_acl_user'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByCedula()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_acl_user",
            'arguments' => "cedula_autorizado = '". $this->formData['cedulaAutorizado'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros para este Autorizado'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function Update()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $updateAutorizado = Database::update([
                    'table'     => "cms_acl_user",
                    'fields'    => [
                        "cms_estados_id_cms_estado"    => $this->formData['estado'],
                        "cedula_autorizado"	            => $this->formData['cedulaAutorizado'],
                        "nombre_autorizado"	            => $this->formData['nombreAutorizado'],
                        "apellidos_autorizado"	        => $this->formData['apellidosAutorizado'],
                        "direccion_autorizado"          => $this->formData['direccionAutorizado'],
                        "telefono_autorizado"           => $this->formData['telefonoAutorizado']
                    ],
                    'arguments' => "id_cms_acl_user = '". $this->formData['id_cms_acl_user'] ."'"                    
                ])->updateRow();

                if($updateAutorizado)
                    return ['status' => true, 'message' => 'Autorizado Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el Autorizado'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function Delete()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $deleteArl = Database::delete([
                    'table'     => "cms_acl_user",                    
                    'arguments' => "id_cms_acl_user = '". $this->formData['id_cms_acl_user'] ."'"
                ])->deleteRow();

                if($deleteArl)
                    return ['status' => true, 'message' => 'Autorizado Eliminado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar el Autorizado'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function UpdateTipoRegistro()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $updateTipoRegistro = Database::update([
                    'table'     => "sg_usuarios",
                    'fields'    => [
                        'id_sg_tipo_registro'   => $this->formData['tiporegistro'],
                        'entrypoint'            => $this->formData['entrypoint']
                    ],
                    'arguments' => "id_sg_usuario = '". ModelGeneral::getIdUserByDecode($this->formData['user']) ."'"
                ])->updateRow();

                $tipo_control = 1;

                if(preg_match('/empresas/', $this->formData['entrypoint']))
                    $tipo_control = 2;

                $updateTipoControl = Database::update([
                    'table'     => "sg_registros",
                    'fields'    => [
                        'id_sg_tipo_control'   => $tipo_control
                    ],
                    'arguments' => "correo = '". ModelGeneral::getCorreoByDecode($this->formData['user']) ."'"
                ])->updateRow();

                if($updateTipoRegistro && $updateTipoControl)
                    return ['status' => true, 'message' => 'Usuario Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el Autorizado'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function UpdateTipoControl()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $updateTipoControl = Database::update([
                    'table'     => "sg_registros",
                    'fields'    => [
                        'id_sg_tipo_control' => $this->formData['control']
                    ],
                    'arguments' => "correo = '". ModelGeneral::getCorreoByDecode($this->formData['user']) ."'"
                ])->updateRow();

                if($this->formData['control'] == 1)
                {
                    Database::update([
                        'table'     => "sg_menu_usuarios",
                        'fields'    => [
                            'href_menu' => '#!/residencial'
                        ],
                        'arguments' => "id_sg_usuario = '". ModelGeneral::getIdUserByDecode($this->formData['user']) ."' AND nombre_menu = 'Mis Visitantes'"
                    ])->updateRow();

                    Database::update([
                        'table'     => "sg_usuarios",
                        'fields'    => [                            
                            'entrypoint'            => '#!/residencial'
                        ],
                        'arguments' => "id_sg_usuario = '". ModelGeneral::getIdUserByDecode($this->formData['user']) ."'"
                    ])->updateRow();
                }

                if($updateTipoControl)
                    return ['status' => true, 'message' => 'Control Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el Control'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }        
    }

    public function ChangeTipoRegistro()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $changeTipoRegistro = Database::update([
                    'table'     => "sg_registros",
                    'fields'    => [
                        'id_sg_tipo_control' => 1
                    ],
                    'arguments' => "correo = '". ModelGeneral::getCorreoByDecode($this->formData['user']) ."'"
                ])->updateRow();

                $changeEntryPoint = Database::update([
                    'table'     => "sg_usuarios",
                    'fields'    => [
                        'entrypoint' => '#!/empresas'
                    ],
                    'arguments' => "correo = '". ModelGeneral::getCorreoByDecode($this->formData['user']) ."'"
                ])->updateRow();

                Database::delete([
                    'table'     => "sg_empresas",                    
                    'arguments' => "id_sg_usuario = '". ModelGeneral::getIdUserByDecode($this->formData['user']) ."'"
                ])->deleteRow();

                Database::delete([
                    'table'     => "sg_sedes",
                    'arguments' => "creado_por = '". $this->formData['user'] ."'"
                ])->deleteRow();

                if($changeTipoRegistro && $changeEntryPoint)
                    return ['status' => true, 'message' => 'Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el Control'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}