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

    public function Create()
    {
        try {

            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                $actividadExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "tipo_actividades",
                    'arguments'  => "nombre_actividad = '". $this->formData['nombreActividad'] ."'"
                ]);

                if($actividadExist)
                    return ['status' => false, 'message' => "La Zona se encuentra registrado"];

                $saveActivity = Database::insert([
                    'table'     => 'tipo_actividades',
                    'values'    => [
                        "cms_empresas_id_cms_empresas"  => $this->formData['idEmpresa'],
                        "cms_estados_id_cms_estados"    => $this->formData['estado'],
                        "cms_acl_user_id_acl_user"      => $this->formData['id_acl_user'],
                        "nombre_actividad"	            => $this->formData['nombreActividad'],
                        "fecha_creacion"                => Database::dateTime()
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

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "id_tipo_actividad as id, nombre_actividad as actividad",
            'table'     => "tipo_actividades",
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
            'table'     => "tipo_actividades",
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
            'table'     => "tipo_actividades",
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
                $updateActividad = Database::update([
                    'table'     => "tipo_actividades",
                    'fields'    => [
                        "cms_empresas_id_cms_empresas"  => $this->formData['idEmpresa'],
                        "cms_estados_id_cms_estados"    => $this->formData['estado'],
                        "cms_acl_user_id_acl_user"      => $this->formData['id_acl_user'],
                        "nombre_actividad"	            => $this->formData['nombreActividad'],
                        "fecha_creacion"                => Database::dateTime()
                    ],
                    'arguments' => "id_tipo_actividad = '". $this->formData['id_tipo_actividad'] ."'"                    
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

    public function Disable()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $disableActividad = Database::update([
                    'table'     => "tipo_actividades",
                    'fields'    => [                        
                        "cms_estados_id_cms_estados"    => $this->formData['estado'],
                        "cms_acl_user_id_acl_user"      => $this->formData['id_acl_user'],
                        "fecha_creacion"                => Database::dateTime()
                    ],
                    'arguments' => "id_tipo_actividad = '". $this->formData['id_tipo_actividad'] ."'"                    
                ])->updateRow();

                if($disableActividad)
                    return ['status' => true, 'message' => 'Actividad Actualizada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al deshabilitar la Actividad'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}