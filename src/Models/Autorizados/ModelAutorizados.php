<?php

namespace Models\Autorizados;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelAutorizados
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
                $autorizadoExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "cms_autorizados",
                    'arguments'  => "cedula_autorizado = '". $this->formData['cedulaAutorizado'] ."'"
                ]);

                if($autorizadoExist)
                    return ['status' => false, 'message' => "Este Autorizado, se encuentra registrado"];

                $saveAutorizado = Database::insert([
                    'table'     => 'cms_autorizados',
                    'values'    => [
                        "cms_estados_id_cms_estados"    => $this->formData['estado'],
                        "cedula_autorizado"	            => $this->formData['cedulaAutorizado'],
                        "nombre_autorizado"	            => $this->formData['nombreAutorizado'],
                        "apellidos_autorizado"	        => $this->formData['apellidosAutorizado'],
                        "direccion_autorizado"          => $this->formData['direccionAutorizado'],
                        "telefono_autorizado"           => $this->formData['telefonoAutorizado']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveAutorizado)
                    return ['status' => true, 'message' => 'Autorizado Registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el Autorizado'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_autorizados",
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros de Autorizados'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "cms_autorizados",
            'arguments' => "id_cms_autorizados = '". $this->formData['id_cms_autorizados'] ."'"
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
            'table'     => "cms_autorizados",
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
                $updateAutorizado = Database::update([
                    'table'     => "cms_autorizados",
                    'fields'    => [
                        "cms_estados_id_cms_estado"    => $this->formData['estado'],
                        "cedula_autorizado"	            => $this->formData['cedulaAutorizado'],
                        "nombre_autorizado"	            => $this->formData['nombreAutorizado'],
                        "apellidos_autorizado"	        => $this->formData['apellidosAutorizado'],
                        "direccion_autorizado"          => $this->formData['direccionAutorizado'],
                        "telefono_autorizado"           => $this->formData['telefonoAutorizado']
                    ],
                    'arguments' => "id_cms_autorizados = '". $this->formData['id_cms_autorizados'] ."'"                    
                ])->updateRow();

                if($updateAutorizado)
                    return ['status' => true, 'message' => 'Autorizado Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el Autorizado'];
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
                    'table'     => "cms_autorizados",                    
                    'arguments' => "id_cms_autorizados = '". $this->formData['id_cms_autorizados'] ."'"
                ])->deleteRow();

                if($deleteArl)
                    return ['status' => true, 'message' => 'Autorizado Eliminado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar el Autorizado'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}