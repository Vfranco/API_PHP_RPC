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

    public function CreateContratista()
    {
        try {

            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                $contratistaExiste = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_personal_proveedor",
                    'arguments'  => "cedula_proveedor = '". $this->formData['cedula'] ."' AND creado_por = '". $this->formData['uid'] ."'"
                ]);

                if($contratistaExiste)
                    return ['status' => false, 'message' => "Este Contratista, se encuentra registrado"];

                $saveContratista = Database::insert([
                    'table'     => 'sg_personal_proveedor',
                    'values'    => [
                        "id_sg_mi_proveedor"    => $this->formData['empresa'],
                        "id_sg_eps"             => $this->formData['eps'],
                        "id_sg_arl"             => $this->formData['arl'],
                        "cedula_proveedor"      => $this->formData['cedula'],
                        "nombres_proveedor"     => $this->formData['nombres'],
                        "correo_proveedor"      => $this->formData['correo'],
                        "expedicion_cedula"     => $this->formData['expedicion'],
                        "estado"                => ($this->formData['estado']) ? 1 : 2,
                        "fecha_creacion"        => Database::dateTime(),
                        "creado_por"            => $this->formData['uid']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveContratista)
                    return ['status' => true, 'message' => 'Contratista Registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el Contratista'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function CreateEmpresa()
    {
        try {

            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                $empresaExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_mis_proveedores",
                    'arguments'  => "nit_proveedor = '". $this->formData['nit'] ."'"
                ]);

                if($empresaExist)
                    return ['status' => false, 'message' => "Esta empresa, se encuentra registrado"];

                $saveEmpresa = Database::insert([
                    'table'     => 'sg_mis_proveedores',
                    'values'    => [
                        "id_sg_usuario"         => ModelGeneral::getIdUserByDecode($this->formData['uid']),
                        "id_sg_estado"          => 1,
                        "nombre_proveedor"      => $this->formData['nombre'],
                        "nit_proveedor"         => $this->formData['nit'],
                        "direccion_proveedor"   => $this->formData['direccion'],
                        "telefono_proveedor"    => $this->formData['telefono'],
                        "correo_proveedor"      => $this->formData['correo'],
                        "fecha_creacion"        => Database::dateTime(),
                        "creado_por"            => $this->formData['uid']
                    ],                    
                    'autoinc'   => true                    
                ])->affectedRow();

                if($saveEmpresa)
                    return ['status' => true, 'message' => 'Empresa Registrada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear la Empresa'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function ReadEmpresas()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_mis_proveedores",
            'arguments' => "creado_por = '". $this->formData['uid'] ."' AND id_sg_estado = 1 ORDER BY fecha_creacion DESC"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function Read()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_mis_proveedores",
            'arguments' => "id_sg_mi_proveedor = '". $this->formData['id'] ."' AND id_sg_estado = 1"
        ])->records()->resultToArray();

        $resultIntegrantes = Database::storeProcedure("CALL obtenerContratistas('". $this->formData['id'] ."')")->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];        

        if(isset($resultIntegrantes[0]['empty']) && $resultIntegrantes[0]['empty'] == true)
            $resultIntegrantes = [];

        return [
            'status'        => true,
            'rows'          => $resultSet,
            'integrantes'   => $resultIntegrantes
        ];
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_mis_proveedores",
            'arguments' => "creado_por = '". $this->formData['uid'] ."' AND id_sg_estado = 1 ORDER BY fecha_creacion DESC"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        $elements = [];

        foreach($resultSet as $i => $item)
        {
            $data = [
                'id'    => (int) $item['id_sg_mi_proveedor'],
                'prop'  => $item['nombre_proveedor']
            ];

            array_push($elements, $data);
        }

        return [
            'status'    => true,
            'combo'     => $elements
        ];
    }

    public function ReadByNit()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_mis_proveedores",
            'arguments' => "nit_proveedor = '". $this->formData['nitProveedor'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByContratista()
    {
        $resultSet = Database::storeProcedure("CALL obtenerDatosContratista('". $this->formData['id'] ."')")->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function UpdateEmpresa()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $updateEmpresa = Database::update([
                    'table'     => "sg_mis_proveedores",
                    'fields'    => [
                        "nombre_proveedor"      => $this->formData['nombre'],
                        "nit_proveedor"         => $this->formData['nit'],
                        "direccion_proveedor"   => $this->formData['direccion'],
                        "telefono_proveedor"    => $this->formData['telefono'],
                        "correo_proveedor"      => $this->formData['correo'],                        
                        "creado_por"            => $this->formData['uid']
                    ],
                    'arguments' => "id_sg_mi_proveedor = '". $this->formData['idempresa'] ."'"
                ])->updateRow();

                if($updateEmpresa)
                    return ['status' => true, 'message' => 'Empresa Actualizada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la Empresa'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function UpdateContratista()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $updateEmpresa = Database::update([
                    'table'     => "sg_personal_proveedor",
                    'fields'    => [
                        "id_sg_mi_proveedor"   => $this->formData['empresa'],
                        "id_sg_eps"            => $this->formData['eps'],
                        "id_sg_arl"            => $this->formData['arl'],
                        "cedula_proveedor"     => $this->formData['cedula'],
                        "nombres_personal"     => $this->formData['nombres'],
                        "apellidos_personal"   => $this->formData['apellidos'],
                        "correo_personal"      => $this->formData['correo'],
                    ],
                    'arguments' => "id_sg_personal_proveedor = '". $this->formData['idcontratista'] ."'"
                ])->updateRow();

                if($updateEmpresa)
                    return ['status' => true, 'message' => 'Empresa Actualizada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la Empresa'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function DisableEmpresa()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $contratistaExiste = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_personal_proveedor",
                    'arguments'  => "id_sg_mi_proveedor = '". $this->formData['id'] ."'"
                ]);

                if($contratistaExiste)
                    return ['status' => false, 'message' => "Tienes Contratistas asociados a esta empresa"];

                $disableEmpresa = Database::update([
                    'table'     => "sg_mis_proveedores",
                    'fields'    => [
                        'id_sg_estado'  => 3,
                    ],
                    'arguments' => "id_sg_mi_proveedor = '". $this->formData['id'] ."'"
                ])->updateRow();

                if($disableEmpresa)
                    return ['status' => true, 'message' => 'Empresa Eliminada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar la Empresa'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function DeleteContratista()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $getVisitsRecords = Database::query([
                    'fields'    => "*",
                    'table'     => "sg_registros_mis_proveedores",
                    'arguments' => "id_sg_personal_proveedor = '". $this->formData['id'] ."'"
                ])->records()->resultToArray();

                if(ModelGeneral::hasRows($getVisitsRecords))
                    return ['status' => false, 'message' => 'Este Contratista contiene registros de E/S en el sistema'];

                $deleteContratista = Database::delete([
                    'table'     => "sg_personal_proveedor",                    
                    'arguments' => "id_sg_personal_proveedor = '". $this->formData['id'] ."'"
                ])->deleteRow();

                if($deleteContratista)
                    return ['status' => true, 'message' => 'Contratista Eliminado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar el Contratista'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}