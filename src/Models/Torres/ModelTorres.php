<?php

namespace Models\Torres;

use Database\Database;
use Core\{Validate};
use Models\Apartamentos\ModelApartamentos;
use Models\General\ModelGeneral;

class ModelTorres
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
                $saveTorre = Database::insert([
                    'table'     => 'sg_torres',
                    'values'    => [                                                
                        "id_sg_empresa"                 => ModelGeneral::getIdEmpresaByUser($this->formData['idUser']),
                        "id_sg_estado"                  => 1,                        
                        "nombreTorre"                   => $this->formData['nombreTorre'],                        
                        "fecha_registro"                => Database::dateTime(),
                        "creado_por"                    => $this->formData['idUser']
                    ],                    
                    'autoinc'   => true                    
                ])->affectedRow();

                $getId = Database::query([
                    'fields'    => "id_sg_torre",
                    'table'     => "sg_torres",
                    'arguments' => "creado_por = '". $this->formData['idUser'] ."' ORDER by id_sg_torre DESC"
                ])->assoc('id_sg_torre');

                if($saveTorre)
                    return ['status' => true, 'message' => 'Torre Registrada', 'lastid' => $getId];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear la Torre'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function CreateNoAplica()
    {
        try {

            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                $saveTorre = Database::insert([
                    'table'     => 'sg_torres',
                    'values'    => [                                                
                        "id_sg_empresa"                 => ModelGeneral::getIdEmpresaByUser($this->formData['idUser']),
                        "id_sg_estado"                  => 1,                        
                        "nombreTorre"                   => 'N/A',
                        "fecha_registro"                => Database::dateTime(),
                        "creado_por"                    => $this->formData['idUser']
                    ],                    
                    'autoinc'   => true                    
                ])->affectedRow();

                $getId = Database::query([
                    'fields'    => "id_sg_torre",
                    'table'     => "sg_torres",
                    'arguments' => "creado_por = '". $this->formData['idUser'] ."' ORDER by id_sg_torre DESC"
                ])->assoc('id_sg_torre');

                if($saveTorre)
                    return ['status' => true, 'message' => 'Torre Registrada', 'lastid' => $getId];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear la Torre'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_torres",
            'arguments' => "creado_por = '". $this->formData['uid'] ."' ORDER BY fecha_registro DESC"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public static function ReadByIdEmpresa($idEmpresa)
    {
        $resultSet = Database::query([
            'fields'    => "id_sg_torre, nombre_torre, creado_por",
            'table'     => "sg_torres",
            'arguments' => "id_sg_empresa = '". $idEmpresa ."' ORDER BY fecha_registro DESC"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'rows' => []];

        $records = [];

        foreach($resultSet as $i => $item)
        {
            $data = [
                'id_sg_torre'   => (int) $item['id_sg_torre'],
                'nombre_torre'  => $item['nombre_torre'],
                'aptos'         => ModelApartamentos::ReadByOwner($item['id_sg_torre'])
            ];

            array_push($records, $data);
        }
        
        return $records;
    }

    public function ReadByIdOficina()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_oficinas",
            'arguments' => "id_sg_oficina = '". $this->formData['id_oficina'] ."' LIMIT 1"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];

        $integrantes = Database::query([
            'fields'    => "so.`nombre_oficina`, so.`piso_oficina`, so.`extension`, smp.`nombres_personal`, smp.`apellidos_personal`",
            'table'     => "sg_personal_oficinas spo JOIN sg_mi_personal smp ON spo.id_sg_personal = smp.`id_sg_personal` JOIN sg_oficinas so ON spo.id_sg_oficina = so.`id_sg_oficina`",
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
                        "id_sg_estado"	                => $this->formData['estadoApartamento'],
                        "piso_apto"	                    => $this->formData['pisoApartamento'],
                        "numero_apto"	                => $this->formData['numeroApartamento'],
                        "fecha_creacion"                => Database::dateTime(),
                        "creado_por"                    => $this->formData['uid']
                    ],
                    'arguments' => "id_sg_apto = '". $this->formData['id_sg_apto'] ."'"
                ])->updateRow();

                if($updateAutorizado)
                    return ['status' => true, 'message' => 'Apartamento Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el Apartamento'];
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
                $towerHasOffices = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_oficinas",
                    'arguments'  => "id_sg_torre = '". $this->formData['idTorre'] ."'"
                ]);

                $towerHasAptos = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_apartamentos",
                    'arguments'  => "id_sg_torre = '". $this->formData['idTorre'] ."'"
                ]);

                if($towerHasOffices)
                    return ['status' => false, 'message' => "Al parecer, tienes oficinas registradas con esta torre"];

                if($towerHasAptos)
                    return ['status' => false, 'message' => "Al parecer, tienes apartamentos registradas con esta torre"];

                $deleteTorre = Database::delete([
                    'table'     => "sg_torres",                    
                    'arguments' => "id_sg_torre = '". $this->formData['idTorre'] ."'"
                ])->deleteRow();

                if($deleteTorre)
                    return ['status' => true, 'message' => 'Torre Eliminada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar la Torre'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}