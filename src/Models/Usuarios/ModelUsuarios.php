<?php

namespace Models\Usuarios;

use Database\Database;
use Core\{Validate};
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
                    'table'      => "cms_acl_user",
                    'arguments'  => "email_acl_user = '". $this->formData['correo'] ."'"
                ]);

                if($usuarioExist)
                    return ['status' => false, 'message' => "Ya tienes una cuenta, puedes iniciar sesión"];

                $saveUsuario = Database::insert([
                    'table'     => 'cms_acl_user',
                    'values'    => [                        
                        "cms_estados_id_cms_estados"    => 1,
                        "cms_plan_id_plan"              => $this->formData['plan'],
                        "cms_empresas_id_cms_empresas"  => 1,
                        "credential_acl_user"	        => Validate::randomNumber(),
                        "fname_acl_user"                => $this->formData['nombres'],
                        "lname_acl_user"                => $this->formData['apellidos'],
                        "password_acl_user"             => $this->formData['pass'],
                        "gender_acl_user"               => 'M',
                        "day_of_birth_acl_user"         => Database::date(),
                        "phone_1_acl_user"              => 0,
                        "address_acl_user"              => "N/A",
                        "email_acl_user"                => $this->formData['correo'],
                        "is_send_email"                 => 1,
                        "date_create_acl_user"          => Database::dateTime(),
                        "ic_acl_user_create"            => 1,
                        "entry_point"                   => "#!/empresas"
                    ],                    
                    'autoinc'   => true                    
                ])->affectedRow();

                $getId = Database::query([
                    'fields'    => "id_acl_user",
                    'table'     => "cms_acl_user",
                    'arguments' => "email_acl_user = '". $this->formData['correo'] ."' ORDER by id_acl_user DESC"
                ])->assoc('id_acl_user');

                foreach(MENU_DEFAULT as $key => $value)
                {
                    $inc = 1;

                    Database::insert(
                    [
                        'table'     => "cms_menu_usuarios",
                        'values'    => [
                            "cms_acl_user_id_acl_user"          => $getId,
                            "cms_estados_id_cms_estados"        => $value['estado'],
                            "nombre_menu"                       => $key,
                            "href_menu"                         => $value['href'],
                            "icon_menu"                         => $value['icon'],
                            "color_menu"                        => $value['color'],
                            "posicion_menu"                     => $inc++
                        ],
                        'autoinc'   => true
                    ])->affectedRow();
                }

                if($saveUsuario)
                    return ['status' => true, 'message' => 'Usuario Registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el Usuario'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
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
}