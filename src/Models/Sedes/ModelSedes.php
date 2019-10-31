<?php

namespace Models\Sedes;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;
use Database\Connect;

class ModelSedes
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
                $saveSede = Database::insert([
                    'table'     => 'sg_sedes',
                    'values'    => [
                        "id_sg_empresa"     => ModelGeneral::getIdEmpresaByUser($this->formData['idUser']),
                        "id_sg_estado"      => 1,
                        "nombre_sede"	    => $this->formData['nombreSede'],
                        "dir_sede"          => $this->formData['dirSede'],
                        "telefono_sede"     => $this->formData['telefonoSede'],
                        "fecha_creacion"    => Database::dateTime(),
                        "creado_por"        => $this->formData['idUser']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveSede)
                    return ['status' => true, 'message' => 'Sede registrada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear la Sede'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "id_sg_sede as id, nombre_sede as sede",
            'table'     => "sg_sedes",
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron Sedes'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_sedes",
            'arguments' => "creado_por = '". $this->formData['uid'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos'];
        
        $elements = [];

        foreach($resultSet as $i => $item)
        {
            $data = [
                'id'    => $item['id_sg_sede'],
                'prop'  => $item['nombre_sede']
            ];

            array_push($elements, $data);
        }

        return [
            'status'    => true,
            'combo'     => $elements
        ];
    }

    public function ReadByIdEmpresa()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_sedes",
            'arguments' => "id_sg_empresa = '". ModelGeneral::getIdEmpresaByUser($this->formData['idusuario']) ."' ORDER BY id_sg_sede DESC"
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
            'table'     => "sg_sedes",
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
                    'table'     => "sg_sedes",
                    'fields'    => [                        
                        "nombre_sede"	                => $this->formData['nombreSede'],
                        "direccion_sede"                => $this->formData['dirSede'],
                        "telefono_sede"                 => $this->formData['telefonoSede']
                    ],
                    'arguments' => "id_sg_sede = '". $this->formData['idSede'] ."'"
                ])->updateRow();

                if($updateSede)
                    return ['status' => true, 'message' => 'Sede Actualizada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la Sede'];
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
                $disableSede = Database::update([
                    'table'     => "sg_sedes",
                    'fields'    => [                        
                        'id_sg_estado'    => $this->formData['estado']
                    ],
                    'arguments' => "id_sg_sede = '". $this->formData['id_cms_sede'] ."'"
                ])->updateRow();

                if($disableSede)
                    return ['status' => true, 'message' => 'Sede Deshabilitada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al deshabilitar la Sede'];
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
                $sedeExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_mi_personal",
                    'arguments'  => "id_sg_sede = '". $this->formData['id_cms_sede'] ."'"
                ]);

                if($sedeExist)
                    return ['status' => false, 'message' => "Tienes un empleado, o empleados asociados a esta sede"];

                $deleteSede = Database::delete([
                    'table'     => "sg_sedes",
                    'arguments' => "id_sg_sede = '". $this->formData['id_cms_sede'] ."'"
                ])->deleteRow();

                if($deleteSede)
                    return ['status' => true, 'message' => 'Sede Eliminada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al deshabilitar la Sede'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }    
}