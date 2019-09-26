<?php

namespace Models\Terminal;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;
use Models\Empleados\ModelEmpleados;

class ModelTerminal
{
    private $formData;
    private $modelGeneral;
    
    public function __construct($formData)
    {
        $this->formData = $formData;
        $this->modelGeneral = new ModelGeneral();
        return $this;
    }

    public function Authentication()
    {
        try {

            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                $hasActiveSession = Database::query([
                    'fields'    => "*",
                    'table'     => "sg_terminales_conectadas",
                    'arguments' => "id_sg_terminal = '". ModelGeneral::getTerminalIdByUserName($this->formData['usuario']) ."'"
                ])->records()->resultToArray();
        
                if(ModelGeneral::hasRows($hasActiveSession))                                    
                    return ['status' => false, 'message' => 'Tienes una sesión activa con esta terminal', 'conexion' => $hasActiveSession[0]['direccion_ip']];                    

                $terminalExist = ModelGeneral::recordExist([
                    'fields'     => "usuario, password",
                    'table'      => "sg_terminal_usuarios",
                    'arguments'  => "usuario = '". Database::escapeSql($this->formData['usuario']) ."' AND '". Database::escapeSql($this->formData['password']) ."'"
                ]);

                if(!$terminalExist)
                    return ['status' => false, 'message' => 'Usuario y/o Contraseña Incorrectas', 'error' => 'Terminal no registrada'];

                $userTerminal = ModelGeneral::getTerminalIdByUserName($this->formData['usuario']);

                $userDataSession = Database::query([
                    'fields'    => "stu.id_sg_terminal_usuario, se.nombre_empresa, ss.nombre_sede, se.id_sg_empresa, ss.id_sg_sede, stu.creado_por, stu.id_sg_estado, stu.creado_por",
                    'table'     => "sg_terminal_usuarios stu JOIN sg_empresas se ON stu.id_sg_empresa = se.id_sg_empresa JOIN sg_sedes ss ON se.id_sg_empresa = ss.id_sg_empresa",
                    'arguments' => "id_sg_terminal_usuario = '". $userTerminal ."' LIMIT 1"
                ])->records()->resultToArray();

                if($userDataSession[0]['id_sg_estado'] != 1)
                    return ['status' => false, 'message' => 'La terminal se encuentra fuera de servicio', 'error' => 'Terminal Inactiva'];

                $formData = Database::query([
                    'fields'    => "*",
                    'table'     => "sg_terminales",
                    'arguments' => "id_sg_terminal_usuario = '". $userTerminal ."'"
                ])->records()->resultToArray();

                Database::insert([
                    'table'     => "sg_terminales_conectadas",
                    'values'    => [
                        'id_sg_terminal'    => $userTerminal,
                        'direccion_ip'      => _REMOTE_ADDR_GENERAL,
                        'fecha_conexion'    => Database::dateTime()
                    ],
                    'autoinc'      => true                    
                ])->affectedRow();

                if(ModelGeneral::hasRows($userDataSession))
                {
                    return [
                        'status'    => true,
                        'empresa'   => [
                            'nombre'    => $userDataSession[0]['nombre_empresa'],
                            'sede'      => $userDataSession[0]['nombre_sede'],
                            'terminal'  => (int) $userDataSession[0]['id_sg_terminal_usuario'],
                            'id_sede'   => (int) $userDataSession[0]['id_sg_sede'],
                            'uid'       => $userDataSession[0]['creado_por']
                        ],
                        'forms'     => [
                            'personal'      => [
                                'status'    => true,
                                'form'      => $formData[0]['formulario'],
                                'arl'       => $this->modelGeneral->getArlList($userDataSession[0]['creado_por']),
                                'eps'       => $this->modelGeneral->getEpsList($userDataSession[0]['creado_por']),
                                'motivos'   => $this->modelGeneral->getMotivoList($userDataSession[0]['creado_por'])
                            ],
                            'visitantes'    => [
                                'status'    => false,
                                'form'      => ''
                            ],
                            'contratistas'  => [
                                'status'    => false,
                                'form'      => ''
                            ]
                        ]
                    ];
                }                
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function CreatePersonal()
    {
        try{
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];

            $obj = new ModelEmpleados($this->formData);
            $response = $obj->Create();

            if($response['status'])
                return $response;
            else
                return $response;

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }        
    }

    public function RegistraActividad()
    {
        try{
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {
                $existMiPersonal = ModelGeneral::recordExist([
                    'fields'    => "*",
                    'table'     => "sg_mi_personal",
                    'arguments' => "cedula_personal = '". Database::escapeSql($this->formData['cedula']) ."' AND id_sg_empresa = '".ModelGeneral::getIdEmpresaByUser($this->formData['uid'])."'"
                ]);

                if(!$existMiPersonal)
                    return ['status' => false, 'message' => 'Empleado no registrado en la Empresa'];

                $existSedePersonal = ModelGeneral::recordExist([
                    'fields'    => "*",
                    'table'     => "sg_mi_personal",
                    'arguments' => "cedula_personal = '". Database::escapeSql($this->formData['cedula']) ."' AND id_sg_sede = '". ModelGeneral::getIdSedeByCedula($this->formData['cedula']) ."'"
                ]);

                if(!$existSedePersonal)
                    return ['status' => false, 'message' => 'Empleado no registrado en esta Sede', 'sede' => ModelGeneral::getNombreSedeById($this->formData['sede'])];

                $existTerminal = ModelGeneral::recordExist([
                    'fields'    => "*",
                    'table'     => "sg_terminales",
                    'arguments' => "id_sg_terminal = '". Database::escapeSql($this->formData['terminal']) ."'"
                ]);

                if(!$existTerminal)
                    return ['status' => false, 'message' => 'Terminal no registrada'];

                $statusEmpleado = Database::query([
                    'fields'    => "id_sg_estado",
                    'table'     => "sg_mi_personal",
                    'arguments' => "cedula_personal = '". $this->formData['cedula'] ."' AND id_sg_empresa = '".ModelGeneral::getIdEmpresaByUser($this->formData['uid'])."' LIMIT 1"
                ])->records()->resultToArray();

                if(ModelGeneral::hasRows($statusEmpleado))
                {
                    if($statusEmpleado[0]['id_sg_estado'] != 1)
                        return ['status' => false, 'message' => 'El empleado ' . ModelGeneral::getNombresEmpleadoByCedula($this->formData['cedula']), 'estado' => 'Inactivo'];
                }

                $hasRecord = Database::query([
                    'fields'    => "*",
                    'table'     => "sg_registros_mi_personal",
                    'arguments' => "id_sg_personal = '". ModelGeneral::getIdPersonalByCedula($this->formData['cedula']) ."' AND id_sg_empresa = '".ModelGeneral::getIdEmpresaByUser($this->formData['uid'])."' ORDER BY id_sg_registro DESC LIMIT 1"
                ])->records()->resultToArray();

                if(isset($this->formData['photo']))
                {
                    Database::update([
                        'table'     => "sg_mi_personal",
                        'fields'    => [
                            'photo_personal' => $this->modelGeneral->uploadImage($this->formData['photo'])
                        ],
                        'arguments' => "cedula_personal = '". $this->formData['cedula'] ."'"
                    ])->updateRow();
                }

                if(ModelGeneral::hasRows($hasRecord))
                {
                    if($hasRecord[0]['fecha_salida'] !== _ERROR_NO_REGISTRA_SALIDA)
                    {
                        $saveActividadIngreso = Database::insert([
                            'table'     => "sg_registros_mi_personal",
                            'values'    => [
                                'id_sg_empresa'     => ModelGeneral::getIdEmpresaByUser($this->formData['uid']),
                                'id_sg_sede'        => $this->formData['sede'],
                                'id_sg_personal'    => ModelGeneral::getIdPersonalByCedula($this->formData['cedula']),
                                'id_sg_visitada'    => ModelGeneral::getIdSedeByTerminal($this->formData['terminal']),                                
                                'fecha_ingreso'     => Database::dateTime(),
                                'fecha_salida'      => '0000-00-00 00:00:00',                                
                                'fecha_registro'    => Database::dateTime()
                            ],
                            'autoinc'   => true                        
                        ])->affectedRow();
    
                        if($saveActividadIngreso)
                            return ['status' => true, 'message' => 'Bienvenido, ' . ModelGeneral::getNombresEmpleadoByCedula($this->formData['cedula']) . ' a la sede ' . ModelGeneral::getNombreSedeById($this->formData['sede']), 'entrada' => Database::dateTime()];
                        else
                            return ['status' => false, 'message' => 'La actividad no pudo ser guardada'];
                    }
                    else
                    {
                        $updateSalida = Database::update([
                            'table'     => "sg_registros_mi_personal",
                            'fields'    => [
                                'fecha_salida'  => Database::dateTime() 
                            ],
                            'arguments' => "id_sg_personal = '". ModelGeneral::getIdPersonalByCedula($this->formData['cedula']) ."' AND id_sg_sede = '". $this->formData['sede'] ."'"
                        ])->updateRow();

                        if($updateSalida)
                            return ['status' => true, 'message' => 'Gracias por tu visita, ' . ModelGeneral::getNombresEmpleadoByCedula($this->formData['cedula']), 'salida' => Database::dateTime()];
                        else
                            return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la salida'];
                    }                    
                }
                else
                {
                    $saveActividadIngreso = Database::insert([
                        'table'     => "sg_registros_mi_personal",
                        'values'    => [
                            'id_sg_empresa'     => ModelGeneral::getIdEmpresaByUser($this->formData['uid']),
                            'id_sg_sede'        => $this->formData['sede'],
                            'id_sg_personal'    => ModelGeneral::getIdPersonalByCedula($this->formData['cedula']),
                            'id_sg_visitada'    => ModelGeneral::getIdSedeByTerminal($this->formData['terminal']),                            
                            'fecha_ingreso'     => Database::dateTime(),
                            'fecha_salida'      => '0000-00-00 00:00:00',                                
                            'fecha_registro'    => Database::dateTime()
                        ],
                        'autoinc'   => true                        
                    ])->affectedRow();

                    if($saveActividadIngreso)
                        return ['status' => true, 'message' => 'Bienvenido, ' . ModelGeneral::getNombresEmpleadoByCedula($this->formData['cedula']) . ' a la sede ' . ModelGeneral::getNombreSedeById($this->formData['sede']), 'entrada' => Database::dateTime()];
                    else
                        return ['status' => false, 'message' => 'La actividad no pudo ser guardada'];
                }
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function Logout()
    {
        $terminalExist = ModelGeneral::recordExist([
            'fields'     => "*",
            'table'      => "sg_terminales_conectadas",
            'arguments'  => "id_sg_terminal = '". Database::escapeSql($this->formData['terminal']) ."'"
        ]);

        if(!$terminalExist)
            return ['status' => false, 'message' => 'No hay terminales conectadas', 'error' => 'Terminal no conectada'];

        $logout = Database::delete([
            'table'     => "sg_terminales_conectadas",
            'arguments' => "id_sg_terminal = '". $this->formData['terminal'] ."'"
        ])->deleteRow();

        if($logout)
            return ['status' => true, 'message' => 'Cierre de session exitoso'];
    }
}