<?php

namespace Models\Movil;

use Database\Database;
use Core\{Validate};
use Models\Movil\{MovilHelper};
use Models\General\ModelGeneral;

class ModelMovil
{
    private $formData;

    public function __construct($formData)
    {
        $this->formData = $formData;
        return $this;
    }

    public function Authentication()
    {
        try {

            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                if(!MovilHelper::isResidente($this->formData['email']))
                    return ['status' => false, 'message' => 'Lo siento, no tienes un correo registrado'];
                else
                {
                    if(MovilHelper::hasCreatedAccount($this->formData['email']))
                    {
                        $dataFromResidente = Database::storeProcedure("CALL getDataFromResidente('". $this->formData['email'] ."')")->records()->resultToArray();
                        return ['welcome' => true, 'route' => '/session', 'uid' => $dataFromResidente[0]['uid']];
                    }                       
                    else
                    {
                        $dataFromResidente = Database::storeProcedure("CALL getDataFromResidente('". $this->formData['email'] ."')")->records()->resultToArray();

                        return [
                            'status'    => true,
                            'correo'    => $dataFromResidente[0]['correo_residente'],
                            'uid'       => $dataFromResidente[0]['uid']
                        ];
                    }                    
                }
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function CreateUserData()
    {
        try {

            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                if(MovilHelper::hasCreatedAccount($this->formData['correo']))
                    return ['status' => false, 'message' => 'Ya tienes una cuenta registrada'];
                else
                {
                    $createUserAccount = Database::insert(
                    [
                        'table'         => "sg_movil_usuarios",
                        'values'        => [
                            'usuario'       => $this->formData['correo'],
                            'password'      => md5($this->formData['passone']),
                            'correo'        => $this->formData['correo'],
                            'fecha_creacion'=> Database::dateTime(),
                            'uid'           => $this->formData['uid'],
                            'intentos'      => 1
                        ],
                        'autoinc'   => true
                    ])->affectedRow();

                    if($createUserAccount)
                        return ['status' => true, 'message' => 'Usuario registrado con exito'];
                    else
                        return ['status' => false, 'message' => 'Oops!, paso algo, por favor intentalo mas tarde'];
                }
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function CreateAutorizado()
    {
        try {

            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                if(MovilHelper::hasAutorizado($this->formData['cedula'], $this->formData['correo']))
                    return ['status' => false, 'message' => 'Este Autorizado ya se encuentra registrado'];
                else
                {
                    if(empty($this->formData['correo']) || empty($this->formData['uid']))
                        return ['status' => false, 'message' => 'Ha ocurrido un error'];
                    else 
                    {
                        $createUserAccount = Database::insert(
                        [
                            'table'         => "sg_movil_autorizados",
                            'values'        => [
                                'cedula'            => $this->formData['cedula'],
                                'nombres'           => $this->formData['nombres'],
                                'apellidos'         => $this->formData['apellidos'],
                                'fecha_creacion'    => Database::dateTime(),
                                'correo'            => $this->formData['correo'],
                                'uid'               => $this->formData['uid']
                            ],
                            'autoinc'   => true
                        ])->affectedRow();

                        if($createUserAccount)
                            return ['status' => true, 'message' => 'Autorizado registrado con exito'];
                        else
                            return ['status' => false, 'message' => 'Oops!, paso algo, por favor intentalo mas tarde'];
                    }
                }
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function ReadAutorizados()
    {
        $autorizados = Database::query([
            'fields'    => "*",
            'table'     => "sg_movil_autorizados",
            'arguments' => "correo = '" . Database::escapeSql($this->formData['correo']) . "' AND uid = '". Database::escapeSql($this->formData['uid']) ."' ORDER BY fecha_creacion DESC"
        ]);

        if(!$autorizados->rows())
            return [[]];

        return $autorizados->records()->resultToArray();    
    }

    public function DeleteAutorizado()
    {
        $deleteAutorizado = Database::delete([
            'table'     => "sg_movil_autorizados",
            'arguments' => "id_sg_autorizado = '". $this->formData['id'] ."'"
        ])->deleteRow();

        if($deleteAutorizado)
            return ['status' => true, 'message' => 'Autorizado Eliminado'];
        else
            return ['status' => false, 'message' => 'Ha ocurrido un error'];
    }
}