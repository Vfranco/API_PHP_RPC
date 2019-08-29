<?php

namespace Models\Unidad;

use Database\Database;
use Core\{Validate, Helper};
use Models\General\ModelGeneral;

class ModelUnidad
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
                $unidadExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_unidad_residencial",
                    'arguments'  => "nit_unidad = '". $this->formData['nitUnidad'] ."' AND telefono_unidad = '". $this->formData['telefonoUnidad'] ."'"
                ]);

                if($unidadExist)
                    return ['status' => false, 'message' => "Ya existe una Unidad con estos datos"];

                $saveUnidad = Database::insert([
                    'table'     => 'sg_unidad_residencial',
                    'values'    => [                                                
                        "id_sg_usuario"                 => ModelGeneral::getIdUserByDecode($this->formData['idUser']),
                        "id_sg_estado"                  => 1,
                        "nit_unidad"                    => $this->formData['nitUnidad'],
                        "nombres_unidad"                => $this->formData['nombreUnidad'],
                        "direccion_unidad"              => $this->formData['direccionUnidad'],
                        "telefono_unidad"               => $this->formData['telefonoUnidad'],
                        "geoposicion"                   => null,                        
                        "fecha_creacion"                => Database::dateTime(),
                        "creado_por"                    => $this->formData['idUser']
                    ],                    
                    'autoinc'   => true                    
                ])->affectedRow();                

                if($saveUnidad)
                    return ['status' => true, 'message' => 'Unidad Registrada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear la Unidad'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}