<?php

namespace Models\Residente;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelResidente
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
                $residenteExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_residentes",
                    'arguments'  => "cedula_residente = '". $this->formData['cedula'] ."'"
                ]);

                if($residenteExist)
                    return ['status' => false, 'message' => "El Rol se encuentra registrado"];

                $saveResidente = Database::insert([
                    'table'     => 'sg_residentes',
                    'values'    => [
                        "cedula_residente"	    => $this->formData['cedula'],
                        "nombres_residente"	    => $this->formData['nombres'],
                        "apellidos_residente"	=> $this->formData['apellidos'],                        
                        "correo_residente"	    => $this->formData['correo'],
                        "fecha_creacion"        => Database::dateTime(),
                        "creado_por"            => $this->formData['uid']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveResidente)
                    return ['status' => true, 'message' => 'Residente Registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el residente'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_residentes",
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
            'table'     => "sg_residentes",
            'arguments' => "id_sg_residente = '". $this->formData['id'] ."'"
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
                    'table'     => "sg_residentes",
                    'fields'    => [
                        "cedula_residente"	    => $this->formData['cedula'],
                        "nombres_residente"	    => $this->formData['nombres'],
                        "apellidos_residente"	=> $this->formData['apellidos'],
                        "correo_residente"	    => $this->formData['correo']
                    ],
                    'arguments' => "id_sg_residente = '". $this->formData['id'] ."'"
                ])->updateRow();

                if($updateRol)
                    return ['status' => true, 'message' => 'Residente Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el residente'];
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
                    'table'     => "sg_residentes",                    
                    'arguments' => "id_sg_residente = '". $this->formData['id'] ."'"
                ])->deleteRow();

                if($deleteRol)
                    return ['status' => true, 'message' => 'Residente Eliminado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar el residente'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}