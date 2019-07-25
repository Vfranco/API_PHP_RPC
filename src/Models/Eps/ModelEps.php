<?php

namespace Models\Eps;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelEps
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
                $epsExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "cms_eps",
                    'arguments'  => "nombre_eps = '". $this->formData['nombreEps'] ."'"
                ]);

                if($epsExist)
                    return ['status' => false, 'message' => "El Rol se encuentra registrado"];

                $saveEps = Database::insert([
                    'table'     => 'cms_eps',
                    'values'    => [
                        "cms_estados_id_cms_estados"    => $this->formData['estado'],
                        "name_acl_role"	                => $this->formData['nombreEps']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveEps)
                    return ['status' => true, 'message' => 'Eps registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear la Eps'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "id_cms_eps as id, nombre_eps as eps",
            'table'     => "cms_eps",
        ])->records()->resultToArray();

        if(isset($resultSet['empty']) && $resultSet['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_eps",
            'arguments' => "id_cms_eps = '". $this->formData['id_cms_eps'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos en la EPS'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByAll()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_eps",
            'arguments' => $this->formData['argument']            
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos en la EPS'];
        
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
                $updateEps = Database::update([
                    'table'     => "cms_eps",
                    'fields'    => [
                        "cms_estados_id_cms_estados"    => $this->formData['estado'],
                        "nombre_eps"	                => $this->formData['nombreEps']
                    ],
                    'arguments' => "id_cms_eps = '". $this->formData['id_cms_eps'] ."'"
                ])->updateRow();

                if($updateEps)
                    return ['status' => true, 'message' => 'EPS Actualizada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la EPS'];
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
                $deleteEps = Database::delete([
                    'table'     => "cms_eps",                    
                    'arguments' => "id_cms_eps = '". $this->formData['id_cms_eps'] ."'"
                ])->deleteRow();

                if($deleteEps)
                    return ['status' => true, 'message' => 'EPS Eliminado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar la EPS'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}