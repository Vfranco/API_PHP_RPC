<?php

namespace Models\Actividades;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelActividades
{
    private $formData;
    
    public function __construct($formData)
    {
        $this->formData = $formData;
        return $this;
    }

    public function CreateActividad()
    {
        try {

            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                $actividadExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_tipos_de_actividad",
                    'arguments'  => "nombre_actividad = '". $this->formData['nombreActividad'] ."'"
                ]);

                if($actividadExist)
                {
                    $enableActividad = Database::update([
                        'table'     => "sg_tipos_de_actividad",
                        'fields'    => [                        
                            "id_sg_estado"  => 1
                        ],
                        'arguments' => "nombre_actividad = '". $this->formData['nombreActividad'] ."'"
                    ])->updateRow();

                    if($enableActividad)
                        return ['status' => true, 'message' => "Actividad registrada"];
                }

                $saveActivity = Database::insert([
                    'table'     => 'sg_tipos_de_actividad',
                    'values'    => [
                        "id_sg_estado"      => 1,
                        "nombre_actividad"  => $this->formData['nombreActividad'],
                        "fecha_creacion"    => Database::dateTime(),
                        "creado_por"	    => $this->formData['uid']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveActivity)
                    return ['status' => true, 'message' => 'Actividad registrada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear la Actividad'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function ReadActividades()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_tipos_de_actividad",
            'arguments' => "creado_por = '". $this->formData['uid'] ."' AND id_sg_estado = 1 ORDER by id_sg_tipo_de_actividad DESC"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron Actividades'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_tipos_de_actividad",
            'arguments' => "id_tipo_actividad = '". $this->formData['id_tipo_actividad'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByAll()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_tipos_de_actividad",
            'arguments' => $this->formData['argument']            
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function UpdateActividad()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $updateActividad = Database::update([
                    'table'     => "sg_tipos_de_actividad",
                    'fields'    => [
                        "nombre_actividad"  => $this->formData['nombreActividad'],
                    ],
                    'arguments' => "id_sg_tipo_de_actividad = '". $this->formData['idActividad'] ."'"
                ])->updateRow();

                if($updateActividad)
                    return ['status' => true, 'message' => 'Actividad Actualizada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la Actividad'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function DeleteActividad()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $disableActividad = Database::update([
                    'table'     => "sg_tipos_de_actividad",
                    'fields'    => [                        
                        "id_sg_estado"  => 3                        
                    ],
                    'arguments' => "id_sg_tipo_de_actividad = '". $this->formData['idactividad'] ."'"
                ])->updateRow();

                if($disableActividad)
                    return ['status' => true, 'message' => 'Actividad Eliminada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar la Actividad'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}