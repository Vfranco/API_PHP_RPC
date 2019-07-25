<?php

namespace Models\Empleados;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelEmpleados
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
                $empleadoExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "cms_empleados",
                    'arguments'  => "cedula_empleado = '". $this->formData['cedula'] ."'"
                ]);

                if($empleadoExist)
                    return ['status' => false, 'message' => "Este Empleado, se encuentra registrado"];

                $saveEmpleado = Database::insert([
                    'table'     => 'cms_empleados',
                    'values'    => [
                        "cms_empresas_id_cms_empresa"   => ModelGeneral::getIdEmpresaByUser($this->formData['idEmpresa']),
                        "cms_estados_id_cms_estado"     => 1,
                        "cms_sede_id_cms_sede"          => $this->formData['sede'],
                        "cedula_empleado"               => $this->formData['cedula'],
                        "nombres_empleado"	            => $this->formData['nombres'],
                        "apellidos_empleado"	        => $this->formData['apellidos'],
                        "direccion_empleado"            => $this->formData['direccion'],
                        "telfono_empleado"              => $this->formData['telefono'],
                        "email_empleado"                => $this->formData['email']
                    ],                    
                    'autoinc'   => true                    
                ])->affectedRow();

                if($saveEmpleado)
                    return ['status' => true, 'message' => 'Empleado Registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el Empleado'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_empleados",
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros de Empleados'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "ce.id_cms_empleado, ce.cedula_empleado, ce.nombres_empleado, ce.apellidos_empleado, ce.direccion_empleado, ce.telefono_empleado, ce.email_empleado, cs.nombre_sede, ce.cms_estados_id_cms_estado, cs.id_cms_sede",
            'table'     => "cms_empleados ce JOIN cms_sedes cs ON ce.cms_sede_id_cms_sede = cs.id_cms_sede",
            'arguments' => "ce.id_cms_empleado = '". $this->formData['id_cms_empleado'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByEmpresa()
    {
        $resultSet = Database::query([
            'fields'    => "ce.id_cms_empleado, ce.nombres_empleado, ce.apellidos_empleado, ce.direccion_empleado, ce.telefono_empleado, ce.email_empleado, cs.nombre_sede, ce.cms_estados_id_cms_estado, ce.cedula_empleado",
            'table'     => "cms_empleados ce JOIN cms_sedes cs ON ce.cms_sede_id_cms_sede = cs.id_cms_sede",
            'arguments' => "ce.cms_empresas_id_cms_empresa = '". ModelGeneral::getIdEmpresaByUser($this->formData['uid']) ."' ORDER BY ce.id_cms_empleado DESC LIMIT 7"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByCedula()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_empleados",
            'arguments' => "cedula_autorizado = '". $this->formData['cedulaAutorizado'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros para este Autorizado'];
        
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
                $updateEmpleado = Database::update([
                    'table'     => "cms_empleados",
                    'fields'    => [                        
                        "cms_sede_id_cms_sede"          => $this->formData['sede'],
                        "cedula_empleado"               => $this->formData['cedula'],
                        "nombres_empleado"	            => $this->formData['nombres'],
                        "apellidos_empleado"	        => $this->formData['apellidos'],
                        "direccion_empleado"            => $this->formData['direccion'],
                        "telefono_empleado"              => $this->formData['telefono'],
                        "email_empleado"                => $this->formData['email']
                    ],
                    'arguments' => "id_cms_empleado = '". $this->formData['id_cms_empleado'] ."'"                    
                ])->updateRow();

                if($updateEmpleado)
                    return ['status' => true, 'message' => 'Empleado Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el Empleado'];
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
                $disableEmpleado = Database::update([
                    'table'     => "cms_empleados",
                    'fields'    => [
                        'cms_estados_id_cms_estado'     => $this->formData['state']
                    ],
                    'arguments' => "id_cms_empleado = '". $this->formData['id_cms_empleado'] ."'"
                ])->updateRow();

                if($disableEmpleado)
                    return ['status' => true, 'message' => 'Empleado Deshabilitado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error'];
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
                $deleteEmpleado = Database::delete([
                    'table'     => "cms_empleados",                    
                    'arguments' => "id_cms_empleado = '". $this->formData['id_cms_empleado'] ."'"
                ])->deleteRow();

                if($deleteEmpleado)
                    return ['status' => true, 'message' => 'Empleado Deshabilitado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}