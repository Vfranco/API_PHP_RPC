<?php

namespace Models\Proveedores;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelProveedores
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
                $proveedorExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "cms_empresa_proveedores",
                    'arguments'  => "nit_proveedor = '". $this->formData['nitProveedor'] ."'"
                ]);

                if($proveedorExist)
                    return ['status' => false, 'message' => "Este Autorizado, se encuentra registrado"];

                $saveProveedor = Database::insert([
                    'table'     => 'cms_empresa_proveedores',
                    'values'    => [
                        "cms_empresas_id_cms_empresas"  => $this->formData['idEmpresa'],
                        "cms_estados_id_cms_estados"    => $this->formData['estado'],
                        "nit_proveedor"                 => $this->formData['nitProveedor'],
                        "nombre_proveedor"	            => $this->formData['nombreProveedor'],
                        "fecha_creacion"	            => Database::dateTime(),
                        "id_acl_user"                   => $this->formData['id_acl_user']
                    ],                    
                    'autoinc'   => true                    
                ])->affectedRow();

                if($saveProveedor)
                    return ['status' => true, 'message' => 'Proveedor Registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el Proveedor'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_empresa_proveedores",
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
            'table'     => "cms_empresa_proveedores",
            'arguments' => "id_cms_empresa_proveedor = '". $this->formData['id_cms_empresa_proveedor'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByNit()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_empresa_proveedores",
            'arguments' => "nit_proveedor = '". $this->formData['nitProveedor'] ."'"
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
            'table'     => "cms_empresa_proveedores",
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
                $updateProveedor = Database::update([
                    'table'     => "cms_empresa_proveedores",
                    'fields'    => [
                        "cms_empresas_id_cms_empresas"  => $this->formData['idEmpresa'],
                        "cms_estados_id_cms_estados"    => $this->formData['estado'],
                        "nit_proveedor"                 => $this->formData['nitProveedor'],
                        "nombre_proveedor"	            => $this->formData['nombreProveedor']
                    ],
                    'arguments' => "id_cms_empresa_proveedor = '". $this->formData['id_cms_empresa_proveedor'] ."'"
                ])->updateRow();

                if($updateProveedor)
                    return ['status' => true, 'message' => 'Proveedor Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el Proveedor'];
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
                    'table'     => "cms_empresa_proveedores",
                    'fields'    => [
                        'cms_empresas_id_cms_empresas'  => $this->formData['idEmpresa'],
                        'cms_estados_id_cms_estados'    => $this->formData['estado']
                    ],
                    'arguments' => "id_cms_empresa_proveedor = '". $this->formData['id_cms_empresa_proveedor'] ."'"
                ])->updateRow();

                if($disableProveedor)
                    return ['status' => true, 'message' => 'Proveedor Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al deshabilitar el Proveedor'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}