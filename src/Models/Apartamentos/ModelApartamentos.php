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
                $apartamentoExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_apartamentos",
                    'arguments'  => "numero_apto = '". $this->formData['numeroApartamento'] ."'"
                ]);

                if($apartamentoExist)
                    return ['status' => false, 'message' => "Este Apartamento, se encuentra registrado"];

                $saveApartamento = Database::insert([
                    'table'     => 'sg_apartamentos',
                    'values'    => [
                        "id_sg_unidad_residencial"      => $this->formData['estado'],
                        "id_sg_estado"	                => $this->formData['estadoApartamento'],
                        "piso_apto"	                    => $this->formData['pisoApartamento'],
                        "numero_apto"	                => $this->formData['numeroApartamento'],
                        "fecha_creacion"                => Database::dateTime(),
                        "creado_por"                    => $this->formData['uid']
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
            'fields'    => "*",
            'table'     => "sg_apartamentos",
            'arguments' => "creado_por = '". $this->formData['uid'] ."'"
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
                $updateAutorizado = Database::update([
                    'table'     => "sg_apartamentos",
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
                $deleteArl = Database::delete([
                    'table'     => "sg_apartamentos",                    
                    'arguments' => "id_sg_apto = '". $this->formData['id_sg_apto'] ."'"
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