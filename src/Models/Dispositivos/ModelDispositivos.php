<?php

namespace Models\Dispositivos;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelDispositivos
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
                $dispositivoExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "cms_dispositivos",
                    'arguments'  => "id_imei_mobile = '". $this->formData['imei'] ."'"
                ]);

                if($dispositivoExist)
                    return ['status' => false, 'message' => "Este Dispositivo, se encuentra registrado"];

                $saveDispositivo = Database::insert([
                    'table'     => 'cms_dispositivos',
                    'values'    => [
                        "cms_empresas_id_cms_empresas"          => $this->formData['idEmpresa'],
                        "cms_estados_id_cms_estados"            => $this->formData['estado'],
                        "cms_comportamiento_id_comportamiento"  => $this->formData['idComportamiento'],
                        "nombre_mobile"	                        => $this->formData['nombreMobile'],
                        "id_imei_mobile"	                    => $this->formData['imei'],
                        "secret_key"	                        => $this->formData['secret_key'],
                        "random_key"                            => $this->formData['random_key'],
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveDispositivo)
                    return ['status' => true, 'message' => 'Dispositivo Registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el Dispositivo'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_dispositivos",
        ])->records()->resultToArray();

        if(isset($resultSet['empty']) && $resultSet['empty'] == true)
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
            'table'     => "cms_dispositivos",
            'arguments' => "id_cms_dispositivo = '". $this->formData['id_cms_dispositivo'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByImei()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_dispositivos",
            'arguments' => "id_imei_mobile = '". $this->formData['imei'] ."'"
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
                $updateDispositivo = Database::update([
                    'table'     => "cms_dispositivos",
                    'fields'    => [
                        "cms_empresas_id_cms_empresas"          => $this->formData['idEmpresa'],
                        "cms_estados_id_cms_estados"            => $this->formData['estado'],
                        "cms_comportamiento_id_comportamiento"  => $this->formData['idComportamiento'],
                        "nombre_mobile"	                        => $this->formData['nombreMobile'],
                        "id_imei_mobile"	                    => $this->formData['imei'],
                        "secret_key"	                        => $this->formData['secret_key'],
                        "random_key"                            => $this->formData['random_key'],
                    ],
                    'arguments' => "id_cms_dispositivo = '". $this->formData['id_cms_dispositivo'] ."'"
                ])->updateRow();

                if($updateDispositivo)
                    return ['status' => true, 'message' => 'Dispositivo Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el Dispositivo'];
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
                $deleteDispositivo = Database::delete([
                    'table'     => "cms_dispositivos",                    
                    'arguments' => "id_cms_dispositivo = '". $this->formData['id_cms_dispositivo'] ."'"
                ])->deleteRow();

                if($deleteDispositivo)
                    return ['status' => true, 'message' => 'Dispositivo Eliminado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar el Dispositivo'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}