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
                $saveEmpleado = Database::insert([
                    'table'     => 'sg_mi_personal',
                    'values'    => [
                        "id_sg_empresa"         => ModelGeneral::getIdEmpresaByUser($this->formData['idEmpresa']),
                        "id_sg_sede"            => $this->formData['sede'],
                        "id_sg_estado"          => 1,
                        "cedula_personal"       => $this->formData['cedula'],
                        "nombres_personal"	    => $this->formData['nombres'],
                        "apellidos_personal"	=> $this->formData['apellidos'],
                        "direccion_personal"    => $this->formData['direccion'],
                        "telfono_personal"      => $this->formData['telefono'],
                        "correo_personal"       => $this->formData['email'],
                        "photo_personal"        => '0',
                        "fecha_registro"        => Database::dateTime(),
                        "creado_por"            => $this->formData['idEmpresa']
                    ],
                    'autoinc'   => true                    
                ])->affectedRow();

                $lastId = Database::query([
                    'fields'    => "id_sg_personal as id, CONCAT(nombres_personal, ' ', apellidos_personal) as prop",
                    'table'     => "sg_mi_personal",
                    'arguments' => "cedula_personal = '". $this->formData['cedula'] ."'"
                ])->records()->resultToArray();

                if($saveEmpleado)
                    return ['status' => true, 'message' => 'Empleado Registrado', 'append' => ['id' => $lastId[0]['id'], 'prop' => $lastId[0]['prop']]];
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
            'fields'    => "se.`id_sg_personal`, se.cedula_personal, se.`nombres_personal`,	se.`apellidos_personal`, se.`direccion_personal`, se.`telefono_personal`, se.`correo_personal`, se.`id_sg_estado`, (SELECT nombre_sede FROM sg_sedes WHERE id_sg_sede = se.id_sg_sede) AS nombre_sede, CONCAT(se.`nombres_personal`, ' ', se.`apellidos_personal`) AS empleado, se.id_sg_sede",
            'table'     => "sg_mi_personal se",
            'arguments' => "se.id_sg_personal = '". $this->formData['id_cms_empleado'] ."'"
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
            'fields'    => "se.`id_sg_personal`, se.cedula_personal, se.`nombres_personal`,	se.`apellidos_personal`, se.`direccion_personal`, se.`telefono_personal`, se.`correo_personal`, se.`id_sg_estado`, (SELECT nombre_sede FROM sg_sedes WHERE id_sg_sede = se.id_sg_sede) AS nombre_sede, CONCAT(se.`nombres_personal`, ' ', se.`apellidos_personal`) AS empleado",
            'table'     => "sg_mi_personal se JOIN sg_sedes ss ON se.id_sg_sede = ss.id_sg_sede",
            'arguments' => "se.id_sg_empresa = '". ModelGeneral::getIdEmpresaByUser($this->formData['uid']) ."' ORDER BY se.id_sg_personal DESC"            
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByName()
    {
        $resultSet = Database::query([
            'fields'    => "ce.id_cms_empleado, CONCAT(ce.nombres_empleado, ' ', ce.apellidos_empleado) as empleado, ce.direccion_empleado, ce.telefono_empleado, ce.email_empleado, cs.nombre_sede, ce.cms_estados_id_cms_estado, ce.cedula_empleado",
            'table'     => "cms_empleados ce JOIN cms_sedes cs ON ce.cms_sede_id_cms_sede = cs.id_cms_sede",
            'arguments' => "ce.nombres_empleado LIKE '". $this->formData['empleado'] ."%' ORDER BY ce.id_cms_empleado DESC"
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
                    'table'     => "sg_mi_personal",
                    'fields'    => [                        
                        "id_sg_sede"            => $this->formData['sede'],                        
                        "cedula_personal"       => $this->formData['cedula'],
                        "nombres_personal"	    => $this->formData['nombres'],
                        "apellidos_personal"	=> $this->formData['apellidos'],
                        "direccion_personal"    => $this->formData['direccion'],
                        "telefono_personal"     => $this->formData['telefono'],
                        "correo_personal"       => $this->formData['email']                        
                    ],
                    'arguments' => "id_sg_personal = '". $this->formData['id_cms_empleado'] ."'"                    
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
                    'table'     => "sg_mi_personal",
                    'fields'    => [
                        'id_sg_estado'     => $this->formData['state']
                    ],
                    'arguments' => "id_sg_personal = '". $this->formData['id_cms_empleado'] ."'"
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
                    'table'     => "sg_mi_personal",
                    'arguments' => "id_sg_personal = '". $this->formData['id_cms_empleado'] ."'"
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