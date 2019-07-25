<?php

namespace Models\Arl;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelArl
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
                $arlExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "cms_arl",
                    'arguments'  => "nombre_arl = '". $this->formData['nombreArl'] ."'"
                ]);

                if($arlExist)
                    return ['status' => false, 'message' => "Esta ARL se encuentra registrada"];

                $saveArl = Database::insert([
                    'table'     => 'cms_arl',
                    'values'    => [
                        "cms_estados_id_cms_estados"    => $this->formData['estado'],
                        "nombre_arl"	                => $this->formData['nombreArl']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveArl)
                    return ['status' => true, 'message' => 'ARL registrada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear la ARL'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "id_cms_arl as id, nombre_arl as arl",
            'table'     => "cms_arl",
        ])->records()->resultToArray();

        if(isset($resultSet['empty']) && $resultSet['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros de ARL'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_arl",
            'arguments' => "id_cms_arl = '". $this->formData['id_cms_arl'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet['empty']) && $resultSet['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros de ARL'];
        
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
                $updateArl = Database::update([
                    'table'     => "cms_arl",
                    'fields'    => [
                        "cms_estados_id_cms_estados"    => $this->formData['estado'],
                        "nombre_arl"	                => $this->formData['nombreArl']
                    ],
                    'arguments' => "id_cms_arl = '". $this->formData['id_cms_arl'] ."'"
                ])->updateRow();

                if($updateArl)
                    return ['status' => true, 'message' => 'ARL Actualizada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la ARL'];
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
                    'table'     => "cms_arl",                    
                    'arguments' => "id_cms_arl = '". $this->formData['id_cms_arl'] ."'"
                ])->deleteRow();

                if($deleteArl)
                    return ['status' => true, 'message' => 'ARL Eliminada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar el ARL'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}