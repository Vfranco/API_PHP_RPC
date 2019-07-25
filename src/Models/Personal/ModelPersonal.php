<?php

namespace Models\Personal;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelPersonal
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
                $personalExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "cms_registro_personal",
                    'arguments'  => "cedula_registro = '". $this->formData['cedulaRegistro'] ."'"
                ]);

                if($personalExist)
                    return ['status' => false, 'message' => "Este Personal, se encuentra registrado"];

                $savePersonal = Database::insert([
                    'table'     => 'cms_registro_personal',
                    'values'    => [
                        "cms_estados_id_cms_estados"                        => $this->formData['estado'],
                        "cms_empresa_proveedores_id_cms_empresa_proveedor"  => $this->formData['idProveedor'],
                        "cedula_registro"	                                => $this->formData['cedulaRegistro'],
                        "nombre_registro"	                                => $this->formData['nombreRegistro'],
                        "apellidos_registros"	                            => $this->formData['apellidosRegistro'],
                        "correo_registro"                                   => $this->formData['correoRegistro'],
                        "celular_registro"                                  => $this->formData['celularRegistro']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($savePersonal)
                    return ['status' => true, 'message' => 'Personal Registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el Personal'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_registro_personal",
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_registro_personal",
            'arguments' => "id_registro_personal = '". $this->formData['id_registro_personal'] ."'"
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
            'table'     => "cms_registro_personal",
            'arguments' => "cedula_registro = '". $this->formData['cedulaRegistro'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByAll()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_registro_personal",
            'arguments' => $this->formData['argument']            
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
                $updatePersonal = Database::update([
                    'table'     => "cms_registro_personal",
                    'fields'    => [
                        "cms_estados_id_cms_estados"                        => $this->formData['estado'],
                        "cms_empresa_proveedores_id_cms_empresa_proveedor"  => $this->formData['idProveedor'],
                        "cedula_registro"	                                => $this->formData['cedulaRegistro'],
                        "nombres_registro"	                                => $this->formData['nombreRegistro'],
                        "apellidos_registros"	                            => $this->formData['apellidosRegistro'],
                        "correo_registro"                                   => $this->formData['correoRegistro'],
                        "celular_registro"                                  => $this->formData['celularRegistro']
                    ],
                    'arguments' => "id_registro_personal = '". $this->formData['id_registro_personal'] ."'"                    
                ])->updateRow();

                if($updatePersonal)
                    return ['status' => true, 'message' => 'Personal Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el Personal'];
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
                $deletePersonal = Database::delete([
                    'table'     => "cms_registro_personal",                    
                    'arguments' => "id_registro_personal = '". $this->formData['id_registro_personal'] ."'"
                ])->deleteRow();

                if($deletePersonal)
                    return ['status' => true, 'message' => 'Personal Eliminado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar el Personal'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}