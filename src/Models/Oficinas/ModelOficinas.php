<?php

namespace Models\Oficinas;

use Database\Database;
use Core\{Validate, Helper};
use Models\General\ModelGeneral;

class ModelOficinas
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
                $saveOficina = Database::insert([
                    'table'     => 'sg_oficinas',
                    'values'    => [
                        "id_sg_torre"                   => $this->formData['idTorre'],
                        "id_sg_empresa"                 => ModelGeneral::getIdEmpresaByUser($this->formData['idUser']),
                        "id_sg_estado"                  => 1,                        
                        "pisoNivel"                     => Helper::dontApply($this->formData['pisoNivel']),
                        "oficina"                       => $this->formData['nombreOficina'],
                        "area"                          => $this->formData['areaOficina'],
                        "fecha_registro"                => Database::dateTime(),
                        "creado_por"                    => $this->formData['idUser']
                    ],                    
                    'autoinc'   => true                    
                ])->affectedRow();

                $getId = Database::query([
                    'fields'    => "id_sg_oficina",
                    'table'     => "sg_oficinas",
                    'arguments' => "creado_por = '". $this->formData['idUser'] ."' ORDER by id_sg_oficina DESC"
                ])->assoc('id_sg_oficina');

                if($saveOficina)
                    return ['status' => true, 'message' => 'Oficina Registrada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear la Oficina'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function AsignaEmpleado()
    {
        try {

            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                $empleadoAsignado = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_personal_oficinas",
                    'arguments'  => "id_sg_personal = '". $this->formData['idEmpleado'] ."'"
                ]);

                if($empleadoAsignado)
                    return ['status' => false, 'message' => "Ya se encuentra asignado en una oficina"];

                $saveAsignacion = Database::insert([
                    'table'     => 'sg_personal_oficinas',
                    'values'    => [
                        "id_sg_oficina"                 => $this->formData['idOficina'],
                        "id_sg_personal"                => $this->formData['idEmpleado'],
                        "fecha_registro"                => Database::dateTime(),
                        "creado_por"                    => $this->formData['idUser']
                    ],                    
                    'autoinc'   => true                    
                ])->affectedRow();

                if($saveAsignacion)
                    return ['status' => true, 'message' => 'Asignacion Registrada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al asignar el Empleado'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_oficinas",
            'arguments' => "creado_por = '". $this->formData['uid'] ."' ORDER BY fecha_registro DESC"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public static function ReadByOwner($owner)
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_oficinas",
            'arguments' => "creado_por = '". $owner ."' ORDER BY fecha_registro DESC"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => []];
        
        $records = [];

        foreach($resultSet as $i => $item)
        {
            $data = [
                'id_sg_oficina' => (int) $item['id_sg_oficina'],
                'oficina'       => $item['oficina'],
                'area'          => $item['area'],
                'estado'        => ($item['id_sg_estado'] == 1) ? 'Activa' : 'Inactiva'
            ];

            array_push($records, $data);
        }

        return $records;
    }

    public function ReadByIdOficina()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_oficinas so JOIN sg_torres st ON so.id_sg_torre = st.id_sg_torre",
            'arguments' => "id_sg_oficina = '". $this->formData['id_oficina'] ."' LIMIT 1"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];

        $integrantes = Database::query([
            'fields'    => "smp.id_sg_personal, so.id_sg_oficina, CONCAT(nombres_personal, ' ', apellidos_personal) as empleado, se.nombre_empresa, so.area, so.oficina, ss.nombre_sede",
            'table'     => "sg_personal_oficinas spo JOIN sg_mi_personal smp ON spo.id_sg_personal = smp.`id_sg_personal` JOIN sg_oficinas so ON spo.id_sg_oficina = so.`id_sg_oficina` JOIN sg_sedes ss ON smp.id_sg_sede = ss.id_sg_sede JOIN sg_empresas se ON smp.id_sg_empresa = se.id_sg_empresa",
            'arguments' => "so.id_sg_oficina = '". $this->formData['id_oficina'] ."'"
        ])->records()->resultToArray();

        if(isset($integrantes[0]['empty']) && $integrantes[0]['empty'] == true)
            $integrantes = [];
        
        return [
            'status'        => true,
            'rows'          => $resultSet,
            'integrantes'   => $integrantes
        ];
    }

    public function ResumenEmpleadosOficina()
    {
        $integrantes = Database::query([
            'fields'    => "smp.cedula_personal, concat(smp.nombres_personal, ' ', smp.apellidos_personal) as empleado, (SELECT nombre_torre FROM sg_torres WHERE id_sg_torre = so.id_sg_torre) as torre, ss.nombre_sede, so.piso_nivel, so.oficina, so.area",
            'table'     => "sg_personal_oficinas spo JOIN sg_mi_personal smp ON spo.id_sg_personal = smp.id_sg_personal JOIN sg_oficinas so ON spo.id_sg_oficina = so.id_sg_oficina JOIN sg_sedes ss ON smp.id_sg_sede = ss.id_sg_sede JOIN sg_empresas se ON smp.id_sg_empresa = se.id_sg_empresa",
            'arguments' => "se.id_sg_empresa = '". ModelGeneral::getIdEmpresaByUser($this->formData['idUser']) ."'"
        ])->records()->resultToArray();

        if(isset($integrantes[0]['empty']) && $integrantes[0]['empty'] == true)
            $integrantes = [];
        
        return [
            'status'        => true,
            'rows'          => $integrantes
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
                $updateAutorizado = Database::update([
                    'table'     => "sg_oficinas",
                    'fields'    => [
                        "id_sg_torre"                   => $this->formData['idTorre'],                        
                        "piso_nivel"                     => Helper::dontApply($this->formData['pisoNivel']),
                        "oficina"                       => $this->formData['nombreOficina'],
                        "area"                          => $this->formData['areaOficina']                        
                    ],
                    'arguments' => "id_sg_oficina = '". $this->formData['idOficina'] ."'"
                ])->updateRow();

                if($updateAutorizado)
                    return ['status' => true, 'message' => 'Oficina Actualizada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la Oficina'];
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
                $oficinasExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_personal_oficinas",
                    'arguments'  => "id_sg_oficina = '". $this->formData['id_sg_oficina'] ."'"
                ]);

                if($oficinasExist)
                    return ['status' => false, 'message' => 'Al Parecer, tienes empleados relacionados en esta oficina'];

                $deleteArl = Database::delete([
                    'table'     => "sg_oficinas",                    
                    'arguments' => "id_sg_oficina = '". $this->formData['id_sg_oficina'] ."'"
                ])->deleteRow();

                if($deleteArl)
                    return ['status' => true, 'message' => 'Oficina Eliminada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar la Oficina'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function DeleteAsignacion()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $deleteEmpleado = Database::delete([
                    'table'     => "sg_personal_oficinas",                    
                    'arguments' => "id_sg_personal = '". $this->formData['id_sg_personal'] ."'"
                ])->deleteRow();

                if($deleteEmpleado)
                    return ['status' => true, 'message' => 'Empleado Eliminado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar el Empleado'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}