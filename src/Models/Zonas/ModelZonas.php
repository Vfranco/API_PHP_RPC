<?php

namespace Models\Zonas;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelZonas
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
                $zonaExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "cms_zonas",
                    'arguments'  => "nombre_zona = '". $this->formData['nombreZona'] ."'"
                ]);

                if($zonaExist)
                    return ['status' => false, 'message' => "La Zona se encuentra registrado"];

                $saveZona = Database::insert([
                    'table'     => 'cms_zonas',
                    'values'    => [
                        "cms_sedes_id_cms_sede"         => $this->formData['idSede'],
                        "cms_estados_id_cms_estados"    => $this->formData['estado'],
                        "nombre_zona"	                => $this->formData['nombreZona'],
                        "datos_geo_cms_zonas"           => $this->formData['geoZona']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveZona)
                    return ['status' => true, 'message' => 'Zona registrada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear la Zona'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "id_cms_zona as id, nombre_zona as zona",
            'table'     => "cms_zonas",
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron Zonas'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_zonas",
            'arguments' => "id_cms_zona = '". $this->formData['id_cms_zona'] ."'"
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
            'table'     => "cms_zonas",
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
                $updateSede = Database::update([
                    'table'     => "cms_zonas",
                    'fields'    => [
                        "cms_sedes_id_cms_sede"         => $this->formData['idSede'],
                        "cms_estados_id_cms_estados"    => $this->formData['estado'],
                        "nombre_zona"	                => $this->formData['nombreZona'],
                        "datos_geo_cms_zonas"           => $this->formData['geoZona']
                    ],
                    'arguments' => "id_cms_zona = '". $this->formData['id_cms_zona'] ."'"
                ])->updateRow();

                if($updateSede)
                    return ['status' => true, 'message' => 'Zona Actualizada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la Zona'];
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
                $disableProveedor = Database::update([
                    'table'     => "cms_zonas",
                    'fields'    => [                        
                        'cms_estados_id_cms_estados'    => $this->formData['estado']
                    ],
                    'arguments' => "id_cms_zona = '". $this->formData['id_cms_zona'] ."'"
                ])->updateRow();

                if($disableProveedor)
                    return ['status' => true, 'message' => 'Zona Deshabilitada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al deshabilitar la Zona'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}