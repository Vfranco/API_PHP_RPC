<?php

namespace Models\Apartamentos;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelApartamentos
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
                $saveApartamento = Database::insert([
                    'table'     => 'sg_apartamentos',
                    'values'    => [
                        "id_sg_unidad_residencial"      => ModelGeneral::getIdUnidadResidencialByUser($this->formData['idUser']),
                        "id_sg_torre"                   => $this->formData['torre'],
                        "id_sg_estado"	                => $this->formData['estado'],
                        "piso_apto"	                    => $this->formData['pisoApartamento'],
                        "numero_apto"	                => $this->formData['numeroApartamento'],
                        "fecha_creacion"                => Database::dateTime(),
                        "creado_por"                    => $this->formData['idUser']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveApartamento)
                    return ['status' => true, 'message' => 'Apartamento Registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el Apartamento'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }    

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "sa.id_sg_apto, st.nombre_torre, sa.piso_apto, sa.numero_apto, sa.fecha_creacion, sa.id_sg_estado",
            'table'     => "sg_apartamentos sa JOIN sg_torres st ON sa.id_sg_torre = st.id_sg_torre",
            'arguments' => "sa.creado_por = '". $this->formData['uid'] ."' ORDER BY id_sg_apto DESC"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }    

    public function ReadByIdApto()
    {
        $resultSet = Database::query([
            'fields'    => "sa.id_sg_apto, st.id_sg_torre, st.nombre_torre, sa.piso_apto, sa.numero_apto, sa.id_sg_estado",
            'table'     => "sg_apartamentos sa JOIN sg_torres st ON sa.id_sg_torre = st.id_sg_torre",
            'arguments' => "sa.id_sg_apto = '". $this->formData['id'] ."'"
        ])->records()->resultToArray();

        $integrantes = Database::query([
            'fields'    => "sra.id_sg_residente, concat(sr.nombres_residente, ' ', sr.apellidos_residente) as residente, st.nombre_torre, sa.numero_apto, sa.piso_apto",
            'table'     => "sg_residentes_apartamento sra JOIN sg_residentes sr ON sra.id_sg_residente = sr.id_sg_residente JOIN sg_torres st ON sra.id_sg_torre = st.id_sg_torre JOIN sg_apartamentos sa ON sra.id_sg_apto = sa.id_sg_apto",
            'arguments' => "sa.id_sg_apto = '".$this->formData['id']."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];

        if(isset($integrantes[0]['empty']) && $integrantes[0]['empty'] == true)
            $integrantes = [];
        
        return [
            'status'        => true,
            'rows'          => $resultSet,
            'residentes'    => $integrantes
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
                $updateApartamento = Database::update([
                    'table'     => "sg_apartamentos",
                    'fields'    => [
                        "id_sg_torre"                   => $this->formData['torre'],
                        "id_sg_estado"	                => $this->formData['estado'],
                        "piso_apto"	                    => $this->formData['pisoApartamento'],
                        "numero_apto"	                => $this->formData['numeroApartamento'],
                    ],
                    'arguments' => "id_sg_apto = '". $this->formData['idApto'] ."'"
                ])->updateRow();

                if($updateApartamento)
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
                $aptoHasResidentes = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_residentes_apartamento",
                    'arguments'  => "id_sg_apto = '". $this->formData['id'] ."'"
                ]);

                if($aptoHasResidentes)
                    return ['status' => false, 'message' => 'Al parecer tienes residentes en este apartamento'];

                $deleteArl = Database::delete([
                    'table'     => "sg_apartamentos",                    
                    'arguments' => "id_sg_apto = '". $this->formData['id'] ."'"
                ])->deleteRow();

                if($deleteArl)
                    return ['status' => true, 'message' => 'Apartamento Eliminado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar el Apartamento'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}