<?php

namespace Models\Roles;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelRoles
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
                $rolExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "cms_acl_role",
                    'arguments'  => "name_acl_role = '". $this->formData['nombreRol'] ."'"
                ]);

                if($rolExist)
                    return ['status' => false, 'message' => "El Rol se encuentra registrado"];

                $saveRol = Database::insert([
                    'table'     => 'cms_acl_role',
                    'values'    => [
                        "name_acl_role"	=> $this->formData['nombreRol']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveRol)
                    return ['status' => true, 'message' => 'Rol registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el rol'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "id_acl_role as id, name_acl_role as rol",
            'table'     => "cms_acl_role",
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron roles'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_acl_role",
            'arguments' => "id_acl_role = '". $this->formData['id_acl_role'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos'];
        
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
                $updateRol = Database::update([
                    'table'     => "cms_acl_role",
                    'fields'    => [
                        "name_acl_role"	=> $this->formData['nombreRol']
                    ],
                    'arguments' => "id_acl_role = '". $this->formData['id_acl_role'] ."'"
                ])->updateRow();

                if($updateRol)
                    return ['status' => true, 'message' => 'Rol Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el rol'];
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
                $deleteRol = Database::delete([
                    'table'     => "cms_acl_role",                    
                    'arguments' => "id_acl_role = '". $this->formData['id_acl_role'] ."'"
                ])->deleteRow();

                if($deleteRol)
                    return ['status' => true, 'message' => 'Rol Eliminado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar el Rol'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}