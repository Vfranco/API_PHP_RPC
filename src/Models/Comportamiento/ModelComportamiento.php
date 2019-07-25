<?php

namespace Models\Comportamiento;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelComportamiento
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
                $comportamientoExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "cms_comportamiento",
                    'arguments'  => "nombre_comportamiento = '". $this->formData['nombreComportamiento'] ."'"
                ]);

                if($comportamientoExist)
                    return ['status' => false, 'message' => "Este Comportamiento se encuentra registrado"];

                $saveComportamiento = Database::insert([
                    'table'     => 'cms_comportamiento',
                    'values'    => [                        
                        "nombre_comportamiento" => $this->formData['nombreComportamiento']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveComportamiento)
                    return ['status' => true, 'message' => 'Comportamiento registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el comportamiento'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "id_cms_comportamiento as id, nombre_comportamiento",
            'table'     => "cms_comportamiento",
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
            'table'     => "cms_comportamiento",
            'arguments' => "id_cms_comportamiento = '". $this->formData['id_cms_comportamiento'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
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
                $updateComportamiento = Database::update([
                    'table'     => "cms_comportamiento",
                    'fields'    => [                        
                        "nombre_comportamiento" => $this->formData['nombreComportamiento']
                    ],
                    'arguments' => "id_cms_comportamiento = '". $this->formData['id_cms_comportamiento'] ."'"
                ])->updateRow();

                if($updateComportamiento)
                    return ['status' => true, 'message' => 'Comportamiento Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el Comportamiento'];
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
                $deleteComportamiento = Database::delete([
                    'table'     => "cms_comportamiento",                    
                    'arguments' => "id_cms_comportamiento = '". $this->formData['id_cms_comportamiento'] ."'"
                ])->deleteRow();

                if($deleteComportamiento)
                    return ['status' => true, 'message' => 'Comportamiento Eliminado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar el Comportamiento'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}