<?php

namespace Models\Configuracion;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelConfiguracion
{
    private $formData;
    
    public function __construct($formData)
    {
        $this->formData = $formData;
        return $this;
    }

    public function CreateEps()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                $saveEps = Database::insert([
                    'table'     => 'sg_eps',
                    'values'    => [
                        "id_sg_estado"          => 1,
                        "nombre_eps"            => $this->formData['nombreEps'],
                        "fecha_creacion"        => Database::dateTime(),
                        "creado_por"	        => $this->formData['uid']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveEps)
                    return ['status' => true, 'message' => 'Eps Registrada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el Eps'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function CreateArl()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                $saveArl = Database::insert([
                    'table'     => 'sg_arl',
                    'values'    => [
                        "id_sg_estado"          => 1,
                        "nombre_arl"            => $this->formData['nombreArl'],
                        "fecha_creacion"        => Database::dateTime(),
                        "creado_por"	        => $this->formData['uid']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveArl)
                    return ['status' => true, 'message' => 'Arl Registrada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el Arl'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function CreateCargo()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                $saveArl = Database::insert([
                    'table'     => 'sg_cargos',
                    'values'    => [
                        "id_sg_estado"          => 1,
                        "nombre_cargo"          => $this->formData['nombreCargo'],
                        "fecha_creacion"        => Database::dateTime(),
                        "creado_por"	        => $this->formData['uid']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                if($saveArl)
                    return ['status' => true, 'message' => 'Cargo Registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el Cargo'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function CreatePersonalControl()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                $personalControlExist = ModelGeneral::recordExist([
                    'fields'    => "*",
                    'table'     => "sg_personal_control",
                    'arguments' => "cedula_control = '". $this->formData['cedula'] ."' LIMIT 1"
                ]);

                if($personalControlExist)
                    return ['status' => false, 'message' => 'El personal de control con CC ' . $this->formData['cedula'] . ' ya se encuentra registrado'];

                $userExistName = ModelGeneral::recordExist([
                    'fields'    => "*",
                    'table'     => "sg_usuarios_control",
                    'arguments' => "usuario = '". $this->formData['usuario'] ."'"
                ]);

                if($userExistName)
                    return ['status' => false, 'message' => "El nombre de usuario (" . $this->formData['usuario'] . ") ya se encuentra registrado"];

                $savePersonalControl = Database::insert([
                    'table'     => 'sg_personal_control',
                    'values'    => [
                        "id_sg_cargo"           => $this->formData['cargo'],
                        "id_sg_estado"          => 1,
                        "cedula_control"        => $this->formData['cedula'],
                        "nombres_control"       => $this->formData['nombres'],
                        "apellidos_control"     => $this->formData['apellidos'],
                        "correo_control"        => $this->formData['correo'],
                        "fecha_creacion"        => Database::dateTime(),
                        "creado_por"	        => $this->formData['uid']
                    ],                    
                    'autoinc'   => true
                ])->affectedRow();

                $lastIdPersonal = Database::query([
                    'fields'    => "id_sg_personal_control",
                    'table'     => "sg_personal_control",
                    'arguments' => "cedula_control = '". $this->formData['cedula'] ."'"
                ])->assoc('id_sg_personal_control');

                $saveUsuarioControl = Database::insert([
                    'table'     => "sg_usuarios_control",
                    'values'    => [
                        'id_sg_personal'    => $lastIdPersonal,
                        'usuario'           => $this->formData['usuario'],
                        'password'          => md5($this->formData['passtwo']),
                        'recovery'          => $this->formData['passtwo'],
                        'intentos'          => 0,
                        'fecha_creacion'    => Database::dateTime(),
                        'creado_por'        => $this->formData['uid']
                    ],
                    'autoinc'   => true
                ])->affectedRow();

                if($savePersonalControl && $saveUsuarioControl)
                    return ['status' => true, 'message' => 'Personal Control Registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el Personal'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function ReadEps()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_eps",
            'arguments' => "creado_por = '". $this->formData['uid'] ."' ORDER BY id_sg_eps DESC"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function DeleteEps()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $deleteTorre = Database::delete([
                    'table'     => "sg_eps",                    
                    'arguments' => "id_sg_eps = '". $this->formData['ideps'] ."'"
                ])->deleteRow();

                if($deleteTorre)
                    return ['status' => true, 'message' => 'EPS Eliminada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar la EPS'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function UpdateEps()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $updateEps = Database::update([
                    'table'     => "sg_eps",                    
                    'fields'    => [
                        'id_sg_eps'     => $this->formData['idEps'],
                        'nombre_eps'    => $this->formData['nombreEps']
                    ],
                    'arguments' => "id_sg_eps = '". $this->formData['idEps'] ."'"
                ])->updateRow();

                if($updateEps)
                    return ['status' => true, 'message' => 'EPS Actualizada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la EPS'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function ReadArl()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_arl",
            'arguments' => "creado_por = '". $this->formData['uid'] ."' ORDER BY fecha_creacion DESC"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function UpdateArl()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $updateArl = Database::update([
                    'table'     => "sg_arl",                    
                    'fields'    => [
                        'id_sg_arl'     => $this->formData['idArl'],
                        'nombre_arl'    => $this->formData['nombreArl']
                    ],
                    'arguments' => "id_sg_arl = '". $this->formData['idArl'] ."'"
                ])->updateRow();

                if($updateArl)
                    return ['status' => true, 'message' => 'ARL Actualizada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la ARL'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function DeleteArl()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $deleteArl = Database::delete([
                    'table'     => "sg_arl",                    
                    'arguments' => "id_sg_arl = '". $this->formData['idarl'] ."'"
                ])->deleteRow();

                if($deleteArl)
                    return ['status' => true, 'message' => 'ARL Eliminada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar la ARL'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function ReadPersonalControl()
    {
        $resultSet = Database::storeProcedure("CALL getListPersonalControl('". $this->formData['uid'] ."')")->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadPersonalById()
    {
        $resultSet = Database::storeProcedure("CALL getPersonalControl('". $this->formData['id'] ."')")->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadCargos()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_cargos",
            'arguments' => "creado_por = '". $this->formData['uid'] ."' ORDER BY fecha_creacion DESC"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function DeleteCargo()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $deleteTorre = Database::delete([
                    'table'     => "sg_cargos",                    
                    'arguments' => "id_sg_cargo = '". $this->formData['idcargo'] ."'"
                ])->deleteRow();

                if($deleteTorre)
                    return ['status' => true, 'message' => 'Cargo Eliminado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar el Cargo'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function UpdateCargo()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $updateArl = Database::update([
                    'table'     => "sg_cargos",
                    'fields'    => [
                        'id_sg_cargo'     => $this->formData['idCargo'],
                        'nombre_cargo'    => $this->formData['nombreCargo']
                    ],
                    'arguments' => "id_sg_cargo = '". $this->formData['idCargo'] ."'"
                ])->updateRow();

                if($updateArl)
                    return ['status' => true, 'message' => 'Cargo Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el Cargo'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function DeletePersonalControl()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $deleteControl = Database::delete([
                    'table'     => "sg_personal_control",
                    'arguments' => "id_sg_personal_control = '". $this->formData['id'] ."'"
                ])->deleteRow();

                if($deleteControl)
                    return ['status' => true, 'message' => 'Personal Eliminado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar el Personal'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function UpdatePersonalControl()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $updatePersonalControl = Database::update([
                    'table'     => "sg_personal_control",
                    'fields'    => [
                        'id_sg_cargo'       => $this->formData['cargo'],
                        'id_sg_estado'      => ($this->formData['status']) ? 1 : 2,
                        'cedula_control'    => $this->formData['cedula'],
                        'nombres_control'   => $this->formData['nombres'],
                        'apellidos_control' => $this->formData['apellidos'],
                        'correo_control'    => $this->formData['correo']                        
                    ],
                    'arguments' => "id_sg_personal_control = '". $this->formData['idControl'] ."'"
                ])->updateRow();

                $updateUsuarioControl = Database::update([
                    'table'     => "sg_usuarios_control",
                    'fields'    => [
                        'usuario'   => $this->formData['usuario'],
                        'password'  => md5($this->formData['passtwo'])
                    ],
                    'arguments' => "id_sg_personal = '". $this->formData['idControl'] ."'"
                ])->updateRow();

                if($updatePersonalControl && $updateUsuarioControl)
                    return ['status' => true, 'message' => 'Personal Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar el Personal'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}