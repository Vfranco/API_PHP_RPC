<?php

namespace Models\Terminal;

use AppLib\Http\Request;
use Database\Database;
use Core\{Validate};
use Exception;
use Models\Apartamentos\ModelApartamentos;
use Models\General\ModelGeneral;
use Models\Empleados\ModelEmpleados;
use Models\Oficinas\ModelOficinas;
use Models\Torres\ModelTorres;
use Models\Proveedores\ModelProveedores;

class ModelTerminal
{
    private $formData;
    private $modelGeneral;
    
    /**
     * 
     */
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
                $terminalExist = ModelGeneral::recordExist([
                    'fields'     => "usuario, password",
                    'table'      => "sg_terminal_usuarios",
                    'arguments'  => "usuario = '". Database::escapeSql($this->formData['usuario']) ."' AND password = '". (Database::escapeSql($this->formData['password'])) ."'"
                ]);

                if(!$terminalExist)
                    return ['status' => false, 'message' => 'Usuario y/o Contraseña Incorrectas', 'error' => 'Terminal no registrada'];

                $hasActiveSession = Database::query([
                    'fields'    => "*",
                    'table'     => "sg_terminales_conectadas",
                    'arguments' => "id_sg_terminal_usuario = '". ModelGeneral::getTerminalIdByUserName($this->formData['usuario']) ."'"
                ])->records()->resultToArray();
        
                if(ModelGeneral::hasRows($hasActiveSession))                                    
                    return ['status' => false, 'message' => 'Tienes una sesión activa con esta terminal', 'conexion' => $hasActiveSession[0]['direccion_ip']];                    

                $terminalExist = ModelGeneral::recordExist([
                    'fields'     => "usuario, password",
                    'table'      => "sg_terminal_usuarios",
                    'arguments'  => "usuario = '". Database::escapeSql($this->formData['usuario']) ."' AND password = '". Database::escapeSql($this->formData['password']) ."'"
                ]);

                if(!$terminalExist)
                    return ['status' => false, 'message' => 'Usuario y/o Contraseña Incorrectas', 'error' => 'Terminal no registrada'];

                $userTerminal = ModelGeneral::getTerminalIdByUserName($this->formData['usuario']);

                $userDataSession = Database::query([
                    'fields'    => "stu.id_sg_terminal_usuario, se.nombre_empresa, ss.nombre_sede, se.id_sg_empresa, ss.id_sg_sede, stu.creado_por, stu.id_sg_estado, stu.creado_por, stu.id_sg_tipo_registro, stu.formulario, stu.id_sg_tipo_control",
                    'table'     => "sg_terminales stu JOIN sg_empresas se ON stu.id_sg_empresa = se.id_sg_empresa JOIN sg_sedes ss ON stu.id_sg_sede = ss.id_sg_sede",
                    'arguments' => "se.id_sg_empresa = '". ModelGeneral::getIdEmpresaByTerminal($userTerminal) ."'"
                ])->records()->resultToArray();

                if($userDataSession[0]['id_sg_estado'] != 1)
                    return ['status' => false, 'message' => 'La terminal se encuentra fuera de servicio', 'error' => 'Terminal Inactiva'];

                Database::insert([
                    'table'     => "sg_terminales_conectadas",
                    'values'    => [
                        'id_sg_terminal_usuario'    => $userTerminal,
                        'direccion_ip'              => _REMOTE_ADDR_GENERAL,
                        'fecha_conexion'            => Database::dateTime()
                    ],
                    'autoinc'      => true                    
                ])->affectedRow();

                if(ModelGeneral::hasRows($userDataSession))
                {
                    foreach($userDataSession as $item => $value)
                    {
                        switch($value['id_sg_tipo_registro'])
                        {
                            case 1:

                                if($value['id_sg_estado'] == 1)
                                {
                                    $formPersonal = [
                                        ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                            'status'    => true,
                                            'form'      => $value['formulario'],
                                            'arl'       => $this->modelGeneral->getArlList($value['creado_por']),
                                            'eps'       => $this->modelGeneral->getEpsList($value['creado_por']),
                                            'motivos'   => $this->modelGeneral->getMotivoList($value['creado_por']),
                                            'sede'      => (int) $value['id_sg_sede']
                                        ]
                                    ];
                                }
                                else
                                {
                                    $formPersonal = [
                                        ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                            'status'    => false,
                                            'form'      => ''
                                        ]
                                    ];
                                }
                            
                            break;

                            case 2:

                                if($value['id_sg_estado'] == 1)
                                {
                                    if($value['id_sg_tipo_control'] == 1)
                                    {                                        
                                        $formVisitantes = [
                                            ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                                'status'    => true,
                                                'form'      => $value['formulario'],
                                                'torres_aptos'    => ModelTorres::ReadByIdEmpresa($value['id_sg_empresa']),                                                
                                                'sede'      => (int)$value['id_sg_sede']
                                            ]
                                        ];
                                    }
                                    else
                                    {
                                        $formVisitantes = [
                                            ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                                'status'    => true,
                                                'form'      => $value['formulario'],
                                                'torres_oficinas'  => ModelOficinas::ReadByOwner($value['creado_por']),
                                                'sede'      => (int)$value['id_sg_sede']
                                            ]
                                        ];
                                    }                               
                                }
                                else
                                {
                                    $formVisitantes = [
                                        ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                            'status'    => false,
                                            'form'      => ''
                                        ]
                                    ];
                                }
                            break;

                            case 3:

                                if($value['id_sg_estado'] == 1)
                                {
                                    $formContratistas = [
                                        ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                            'status'        => true,
                                            'form'          => $value['formulario'],
                                            'arl'           => $this->modelGeneral->getArlList($value['creado_por']),
                                            'eps'           => $this->modelGeneral->getEpsList($value['creado_por']),
                                            'actividades'   => $this->modelGeneral->getActividadesList($value['creado_por']),
                                            'empresas'      => $this->modelGeneral->getEmpresasList($value['creado_por']),
                                            'sede'          => (int)$value['id_sg_sede']
                                            
                                        ]
                                    ];
                                }
                                else 
                                {
                                    $formContratistas = [
                                        ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                            'status'    => false,
                                            'form'      => ''
                                        ]
                                    ];
                                }
                                
                            break;
                        }
                    }

                    if(empty($formPersonal['personal']))
                        $formPersonal['personal'] = ['status' => false, 'form' => ''];

                    if(empty($formVisitantes['visitantes']))
                        $formPersonal['visitantes'] = ['status' => false, 'form' => ''];

                    if(empty($formContratistas['contratistas']))
                        $formPersonal['contratistas'] = ['status' => false, 'form' => ''];
                    
                    return [
                        'status'    => true,
                        'empresa'   => [
                            'nombre'        => $userDataSession[0]['nombre_empresa'],
                            'sede'          => $userDataSession[0]['nombre_sede'],
                            'terminal'      => (int) $userTerminal,
                            'id_sede'       => (int) $userDataSession[0]['id_sg_sede'],
                            'uid'           => $userDataSession[0]['creado_por'],
                            'tipo_control'  => (int) $userDataSession[0]['id_sg_tipo_control']
                        ],
                        'forms'     => [
                            'personal'      => $formPersonal['personal'],
                            'visitantes'    => $formVisitantes['visitantes'],
                            'contratistas'  => $formContratistas['contratistas']
                        ]
                    ];
                }                
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function Logout()
    {
        $terminalExist = ModelGeneral::recordExist([
            'fields'     => "*",
            'table'      => "sg_terminales_conectadas",
            'arguments'  => "id_sg_terminal_usuario = '". Database::escapeSql($this->formData['terminal']) ."'"
        ]);

        if(!$terminalExist)
            return ['status' => false, 'message' => 'No hay terminales conectadas', 'error' => 'Terminal no conectada'];

        $logout = Database::delete([
            'table'     => "sg_terminales_conectadas",
            'arguments' => "id_sg_terminal_usuario = '". $this->formData['terminal'] ."'"
        ])->deleteRow();

        if($logout)
            return ['status' => true, 'message' => 'Cierre de session exitoso'];
    }

    public function CheckMiPersonal()
    {
        $checkPersonal = ModelGeneral::recordExist([
            'fields'    => "*",
            'table'     => "sg_mi_personal",
            'arguments' => "cedula_personal = '". $this->formData['cedula'] ."'"
        ]);

        if(!$checkPersonal)
            return ['status' => false, 'message' => 'Esta cedula, no se encuentra registrada'];
        else
        {
            $visitasHistoric = Database::query([
                'fields'    => "*",
                'table'     => "sg_registros_mi_personal srmp JOIN sg_mi_personal smp ON srmp.id_sg_personal = smp.id_sg_personal",
                'arguments' => "srmp.id_sg_personal = '". ModelGeneral::getIdPersonalByCedula($this->formData['cedula']) ."' ORDER BY smp.fecha_creacion DESC LIMIT 1"                
            ])->records()->resultToArray();

            if(!ModelGeneral::hasRows($visitasHistoric))
            {
                return [
                    'status'        => true,
                    'personal'      => ''                    
                ];
            }

            $hasSalidas = ModelGeneral::recordExist([
                'fields'    => "*",
                'table'     => "sg_registros_mi_personal",
                'arguments' => "id_sg_personal = '". ModelGeneral::getIdPersonalByCedula($this->formData['cedula']) ."' AND fecha_salida = '0000-00-00 00:00:00' AND estado_salida = 0"
            ]);

            if($hasSalidas)
            {
                $salida = Database::update([
                    'table'      => "sg_registros_mi_personal",
                    'fields'      => [
                        'fecha_salida'      => Database::dateTime(),
                        'estado_salida'     => 1
                    ],
                    'arguments' => "id_sg_personal = '". ModelGeneral::getIdPersonalByCedula($this->formData['cedula']) ."' AND fecha_salida = '0000-00-00 00:00:00' AND estado_salida = 0"
                ])->updateRow();

                if($salida)
                {
                    Request::getRequest(NODE_SERVER . '/monitor/personal/' . $this->formData['uid']);

                    return [
                        'status'        => true,
                        'personal'      => '',
                        'message'       => 'Gracias ' . $visitasHistoric[0]['nombres_personal'] . ' por tu visita!',
                        'ultima_visita' => (empty($visitasHistoric[0]['fecha_ingreso'])) ? '' : $visitasHistoric[0]['fecha_ingreso']
                    ];
                }
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error'];
            }
            else
            {
                Request::getRequest(NODE_SERVER . '/monitor/personal/' . $this->formData['uid']);

                return [
                    'status'        => true,
                    'personal'      => $visitasHistoric[0]['nombres_personal'] . " " . $visitasHistoric[0]['apellidos_personal'],
                    'message'       => 'No tiene registros de entrada',
                    'ultima_visita' => (empty($visitasHistoric[0]['fecha_ingreso'])) ? '' : $visitasHistoric[0]['fecha_ingreso']
                ];
            }
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
            {
                $saveActividadIngreso = Database::insert([
                    'table'     => "sg_registros_mi_personal",
                    'values'    => [
                        'id_sg_empresa'     => ModelGeneral::getIdEmpresaByUser($this->formData['idEmpresa']),
                        'id_sg_sede'        => $this->formData['sede'],
                        'id_sg_personal'    => ModelGeneral::getIdPersonalByCedula($this->formData['cedula']),
                        'id_sg_visitada'    => ModelGeneral::getIdSedeByTerminal($this->formData['terminal']),                                
                        'fecha_ingreso'     => Database::dateTime(),
                        'fecha_salida'      => _ERROR_NO_REGISTRA_SALIDA,
                        'estado_salida'     => 0,
                        'fecha_registro'    => Database::dateTime(),
                        'creado_por'        => $this->formData['idEmpresa']
                    ],
                    'autoinc'   => true                        
                ])->affectedRow();

                if($saveActividadIngreso)
                {
                    Request::getRequest(NODE_SERVER . '/monitor/personal/' . $this->formData['idEmpresa']);
                    return ['status' => true, 'message' => 'Bienvenido, ' . ModelGeneral::getNombresEmpleadoByCedula($this->formData['cedula']) . ' a la sede ' . ModelGeneral::getNombreSedeById($this->formData['sede']), 'entrada' => Database::dateTime()];
                }
                else
                    return ['status' => false, 'message' => 'La actividad no pudo ser guardada'];
            }


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

                $checkTerminalUid = Database::query([
                    'fields'    => "*",
                    'table'     => "sg_terminal_usuarios",
                    'arguments' => "id_sg_terminal_usuario = '". $this->formData['terminal'] ."'"
                ])->records()->resultToArray();

                if($this->formData['uid'] != $checkTerminalUid[0]['creado_por'])
                    return ['status' => false, 'message' => 'El empleado que desea validar, no pertenece a esta empresa'];  

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
                    'arguments' => "id_sg_personal = '". ModelGeneral::getIdPersonalByCedula($this->formData['cedula']) ."' AND id_sg_empresa = '".ModelGeneral::getIdEmpresaByUser($this->formData['uid'])."' AND fecha_salida = '". _ERROR_NO_REGISTRA_SALIDA ."' ORDER BY id_sg_registro DESC LIMIT 1"
                ])->records()->resultToArray();                

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
                                'fecha_salida'      => _ERROR_NO_REGISTRA_SALIDA,
                                'estado_salida'     => 0,
                                'fecha_registro'    => Database::dateTime(),
                                'creado_por'        => $this->formData['uid']
                            ],
                            'autoinc'   => true                        
                        ])->affectedRow();                        
    
                        if($saveActividadIngreso)
                        {
                            Request::getRequest(NODE_SERVER . '/monitor/personal/' . $this->formData['uid']);
                            return ['status' => true, 'message' => 'Bienvenido, ' . ModelGeneral::getNombresEmpleadoByCedula($this->formData['cedula']) . ' a la sede ' . ModelGeneral::getNombreSedeById($this->formData['sede']), 'entrada' => Database::dateTime()];
                        }
                        else
                            return ['status' => false, 'message' => 'La actividad no pudo ser guardada'];
                    }
                    else
                    {
                        $updateSalida = Database::update([
                            'table'     => "sg_registros_mi_personal",
                            'fields'    => [
                                'fecha_salida'      => Database::dateTime(),
                                'estado_salida'     => 1
                            ],
                            'arguments' => "id_sg_personal = '". ModelGeneral::getIdPersonalByCedula($this->formData['cedula']) ."' AND id_sg_sede = '". $this->formData['sede'] ."'"
                        ])->updateRow();                        

                        if($updateSalida)
                        {
                            Request::getRequest(NODE_SERVER . '/monitor/personal/' . $this->formData['uid']);
                            return ['status' => true, 'message' => 'Gracias por tu visita, ' . ModelGeneral::getNombresEmpleadoByCedula($this->formData['cedula']), 'salida' => Database::dateTime()];
                        }
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
                            'fecha_salida'      => _ERROR_NO_REGISTRA_SALIDA,
                            'estado_salida'     => 0,
                            'fecha_registro'    => Database::dateTime(),
                            'creado_por'        => $this->formData['uid']
                        ],
                        'autoinc'   => true                        
                    ])->affectedRow();

                    if($saveActividadIngreso)
                    {
                        Request::getRequest(NODE_SERVER . '/monitor/personal/' . $this->formData['uid']);
                        return ['status' => true, 'message' => 'Bienvenido, ' . ModelGeneral::getNombresEmpleadoByCedula($this->formData['cedula']) . ' a la sede ' . ModelGeneral::getNombreSedeById($this->formData['sede']), 'entrada' => Database::dateTime()];
                    }
                    else
                        return ['status' => false, 'message' => 'La actividad no pudo ser guardada'];
                }
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function checkVisitante()
    {
        $chekVisitante = ModelGeneral::recordExist([
            'fields'    => "*",
            'table'     => "sg_mis_visitantes",
            'arguments' => "cedula = '". $this->formData['cedula'] ."'"
        ]);

        if(!$chekVisitante)
            return ['status' => false, 'message' => 'No existe el visitante'];
        else
        {
            $visitasHistoric = Database::query([
                'fields'    => "smv.nombres_visitante, srmv.fecha_visita",
                'table'     => "sg_registros_mis_visitantes srmv JOIN sg_mis_visitantes smv ON srmv.id_sg_visitante = smv.id_sg_visitante",
                'arguments' => "srmv.id_sg_visitante = '". ModelGeneral::getIdVisitanteByCedula($this->formData['cedula']) ."' ORDER BY fecha_registro DESC LIMIT 1"
            ])->records()->resultToArray();

            $hasSalidas = ModelGeneral::recordExist([
                'fields'    => "*",
                'table'     => "sg_registros_mis_visitantes",
                'arguments' => "id_sg_visitante = '". ModelGeneral::getIdVisitanteByCedula($this->formData['cedula']) ."' AND salida_visita = '0000-00-00 00:00:00' AND estado_salida = '0'"
            ]);

            if($hasSalidas)
            {
                $salida = Database::update([
                    'table'      => "sg_registros_mis_visitantes",
                    'fields'      => [
                        'salida_visita'  => Database::dateTime(),
                        'estado_salida'  => 1
                    ],
                    'arguments' => "id_sg_visitante = '". ModelGeneral::getIdVisitanteByCedula($this->formData['cedula']) ."' AND salida_visita = '0000-00-00 00:00:00' AND estado_salida = 0"
                ])->updateRow();

                if($salida)
                {
                    Request::getRequest(NODE_SERVER . '/monitor/visitantes/' . $this->formData['uid']);

                    return [
                        'status'        => true,
                        'visitante'     => '',
                        'message'       => 'Gracias ' . $visitasHistoric[0]['nombres_visitante'] . ' por tu visita!',
                        'ultima_visita' => (empty($visitasHistoric[0]['fecha_visita'])) ? '' : $visitasHistoric[0]['fecha_visita']
                    ];
                }
            }
            else
            {
                Request::getRequest(NODE_SERVER . '/monitor/visitantes/' . $this->formData['uid']);

                return [
                    'status'        => true,
                    'visitante'     => $visitasHistoric[0]['nombres_visitante'],
                    'message'       => 'No tiene registros de entrada',
                    'ultima_visita' => (empty($visitasHistoric[0]['fecha_visita'])) ? '' : $visitasHistoric[0]['fecha_visita']
                ];
            }
        }
    }

    public function CreateVisitante()
    {
        try{
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Campos Requeridos'];
            else
            {
                $visitanteExist = ModelGeneral::recordExist([
                    'fields'    => "*",
                    'table'     => "sg_mis_visitantes",
                    'arguments' => "cedula = '". $this->formData['cedula'] ."'"
                ]);

                if($visitanteExist)
                {
                    $hasVisita = ModelGeneral::recordExist([
                        'fields'    => "*",
                        'table'     => "sg_registros_mis_visitantes",
                        'arguments' => "id_sg_visitante = '". ModelGeneral::getIdVisitanteByCedula($this->formData['cedula']) ."' AND id_sg_torre = '".$this->formData['idtorre']."' AND id_sg_apto = '". $this->formData['idaptooficina'] ."' AND estado_salida = 0"
                    ]);
    
                    if($hasVisita)
                        return ['status' => false, 'message' => 'Este visitante cuenta con un registro, por favor dele salida'];

                    $lastIdVisitante = Database::query([
                        'fields'    => "id_sg_visitante",
                        'table'     => "sg_mis_visitantes",
                        'arguments' => "cedula = '". $this->formData['cedula'] ."'"
                    ])->records()->resultToArray();
                    
                    $registraVisitaResidencial = Database::insert([
                        'table'     => "sg_registros_mis_visitantes",
                        'values'    => [
                            'id_sg_visitante'           => (int) $lastIdVisitante[0]['id_sg_visitante'],
                            'id_sg_terminal_usuario'    => $this->formData['terminal'],
                            'id_sg_tipo_control'        => $this->formData['tipo_control'],
                            'id_sg_torre'               => $this->formData['idtorre'],
                            'id_sg_apto'                => $this->formData['idaptooficina'],
                            'fecha_visita'              => Database::dateTime(),
                            'salida_visita'             => _ERROR_NO_REGISTRA_SALIDA,
                            'estado_salida'             => 0,
                            'fecha_registro'            => Database::dateTime(),
                            'photo'                     => 0,
                            'creado_por'                => $this->formData['uid']
                        ],
                        'autoinc'  => true                    
                    ])->affectedRow();                    
    
                    if($registraVisitaResidencial)
                    {
                        Request::getRequest(NODE_SERVER . '/monitor/visitantes/' . $this->formData['uid']);
                        return ['status' => true, 'message' => 'Visitante registrado exitosamente'];
                    }
                    else
                        return ['status' => false, 'message' => 'Ha ocurrido un error al registrar el visitante'];
                }
                else
                {
                    $hasVisita = ModelGeneral::recordExist([
                        'fields'    => "*",
                        'table'     => "sg_registros_mis_visitantes",
                        'arguments' => "id_sg_visitante = '". ModelGeneral::getIdVisitanteByCedula($this->formData['cedula']) ."' AND id_sg_torre = '".$this->formData['idtorre']."' AND id_sg_apto = '". $this->formData['idaptooficina'] ."' AND estado_salida = 0"
                    ]);
    
                    if($hasVisita)
                        return ['status' => false, 'message' => 'Este visitante cuenta con un registro, por favor dele salida'];

                    $registraVisitante = Database::insert([
                        'table'     => "sg_mis_visitantes",
                        'values'    => [
                            'cedula'            => (int) $this->formData['cedula'],
                            'nombres_visitante' => $this->formData['nombres'],
                            'fecha_visita'      => Database::dateTime(),
                            'registrado_por'    => $this->formData['uid']
                        ],
                        'autoinc'  => true                    
                    ])->affectedRow();
    
                    $lastIdVisitante = Database::query([
                        'fields'    => "id_sg_visitante",
                        'table'     => "sg_mis_visitantes",
                        'arguments' => "cedula = '". $this->formData['cedula'] ."'"
                    ])->records()->resultToArray();
                    
                    $registraVisitaResidencial = Database::insert([
                        'table'     => "sg_registros_mis_visitantes",
                        'values'    => [
                            'id_sg_visitante'           => (int) $lastIdVisitante[0]['id_sg_visitante'],
                            'id_sg_terminal_usuario'    => $this->formData['terminal'],
                            'id_sg_tipo_control'        => $this->formData['tipo_control'],
                            'id_sg_torre'               => $this->formData['idtorre'],
                            'id_sg_apto'                => $this->formData['idaptooficina'],
                            'fecha_visita'              => Database::dateTime(),
                            'salida_visita'             => _ERROR_NO_REGISTRA_SALIDA,
                            'estado_salida'             => 0,
                            'fecha_registro'            => Database::dateTime(),
                            'photo'                     => 0,
                            'creado_por'                => $this->formData['uid']
                        ],
                        'autoinc'  => true                    
                    ])->affectedRow();                    

                    if($registraVisitante && $registraVisitaResidencial)
                    {
                        Request::getRequest(NODE_SERVER . '/monitor/visitantes/' . $this->formData['uid']);
                        return ['status' => true, 'message' => 'Visitante registrado exitosamente here'];
                    }
                    else
                        return ['status' => false, 'message' => 'Ha ocurrido un error al registrar el visitante'];
                }
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function CheckContratista()
    {
        $contratistaExiste = ModelGeneral::recordExist([
            'fields'     => "*",
            'table'      => "sg_personal_proveedor",
            'arguments'  => "cedula_proveedor = '". $this->formData['cedula'] ."'"
        ]);

        if(!$contratistaExiste)
            return ['status' => false, 'message' => "No se encontraron datos de este Contratista"];
        else
        {
            $visitasHistoric = Database::query([
                'fields'    => "*",
                'table'     => "sg_registros_mis_proveedores srmp JOIN sg_personal_proveedor spp ON srmp.id_sg_personal_proveedor = spp.id_sg_personal_proveedor",
                'arguments' => "srmp.id_sg_personal_proveedor = '". ModelGeneral::getIdContratistaByCedula($this->formData['cedula']) ."' ORDER BY srmp.fecha_registro DESC LIMIT 1"
            ])->records()->resultToArray();

            if(isset($visitasHistoric[0]['empty']) && $visitasHistoric[0]['empty'] == true)
            {
                $getDataContratista = Database::query([
                    'fields'    => "*",
                    'table'     => "sg_personal_proveedor",
                    'arguments' => "cedula_proveedor = '". $this->formData['cedula'] ."'"
                ])->records()->resultToArray();

                if(ModelGeneral::hasRows($getDataContratista))
                {
                    return [
                        'status'        => true,
                        'contratista'   => (!isset($getDataContratista[0]['nombres_personal'])) ? '' : $getDataContratista[0]['nombres_personal'],
                        'eps'           => 0,
                        'arl'           => 0,
                        'actividad'     => 0,
                        'torre'         => 0,
                        'aptooficina'   => 0,
                        'empresa'       => (!isset($getDataContratista[0]['id_sg_mi_proveedor'])) ? 0 : (int) $getDataContratista[0]['id_sg_mi_proveedor'],
                        'message'       => 'No tiene registros de entrada',
                        'ultima_visita' => (empty($visitasHistoric[0]['fecha_entrada'])) ? '' : $visitasHistoric[0]['fecha_entrada'],
                        'expedicion'    => (!isset($getDataContratista[0]['expedicion_cedula'])) ? '' : $getDataContratista[0]['expedicion_cedula'],
                        'correo'        => (!isset($getDataContratista[0]['correo_personal'])) ? '' : $getDataContratista[0]['correo_personal'],
                        'status_io'     => false
                    ];
                }
            }

            $hasSalidas = ModelGeneral::recordExist([
                'fields'    => "*",
                'table'     => "sg_registros_mis_proveedores",
                'arguments' => "id_sg_personal_proveedor = '". ModelGeneral::getIdContratistaByCedula($this->formData['cedula']) ."' AND fecha_salida = '0000-00-00 00:00:00' AND estado_salida = '0'"
            ]);

            $getDataContratista = Database::storeProcedure("CALL getDataFromContratista('". $this->formData['cedula'] ."')")->records()->resultToArray();

            if($hasSalidas)
            {
                $salida = Database::update([
                    'table'      => "sg_registros_mis_proveedores",
                    'fields'      => [
                        'fecha_salida'      => Database::dateTime(),
                        'estado_salida'     => 1
                    ],
                    'arguments' => "id_sg_personal_proveedor = '". ModelGeneral::getIdContratistaByCedula($this->formData['cedula']) ."' AND fecha_salida = '0000-00-00 00:00:00' AND estado_salida = 0"
                ])->updateRow();

                Database::update([
                    'table'     => "sg_personal_proveedor",
                    'fields'    => [
                        'id_sg_eps' => $this->formData['eps'],
                        'id_sg_arl' => $this->formData['arl']
                    ],
                    'arguments' => "cedula_proveedor = '". $this->formData['cedula'] ."'"
                ])->updateRow();

                if($salida)
                {
                    Request::getRequest(NODE_SERVER . '/monitor/contratistas/' . $this->formData['uid']);

                    return [
                        'status'        => true,
                        'contratista'   => $visitasHistoric[0]['nombres_personal'],
                        'eps'           => (int) $visitasHistoric[0]['id_sg_eps'],
                        'arl'           => (int) $visitasHistoric[0]['id_sg_arl'],
                        'actividad'     => (int) $visitasHistoric[0]['id_sg_tipo_de_actividad'],
                        'torre'         => (int) $visitasHistoric[0]['id_sg_torre'],
                        'aptooficina'   => (int) $visitasHistoric[0]['id_apto_oficina'],
                        'empresa'       => (int) $visitasHistoric[0]['id_sg_mi_proveedor'],
                        'message'       => 'Gracias ' . $visitasHistoric[0]['nombres_personal'],
                        'ultima_visita' => (empty($visitasHistoric[0]['fecha_entrada'])) ? '' : $visitasHistoric[0]['fecha_entrada'],
                        'expedicion'    => ModelGeneral::changeFormatDate($visitasHistoric[0]['expedicion_cedula']),
                        'correo'        => $visitasHistoric[0]['correo_personal'],
                        'status_io'     => true
                    ];
                }
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la salida'];
            }
            else
            {
                Request::getRequest(NODE_SERVER . '/monitor/contratistas/' . $this->formData['uid']);

                return [
                    'status'        => true,
                    'contratista'   => $visitasHistoric[0]['nombres_personal'],
                    'eps'           => (int) $visitasHistoric[0]['id_sg_eps'],
                    'arl'           => (int) $visitasHistoric[0]['id_sg_arl'],
                    'actividad'     => (int) $visitasHistoric[0]['id_sg_tipo_de_actividad'],
                    'torre'         => (int) $visitasHistoric[0]['id_sg_torre'],
                    'aptooficina'   => (int) $visitasHistoric[0]['id_apto_oficina'],
                    'empresa'       => (int) $visitasHistoric[0]['id_sg_mi_proveedor'],
                    'message'       => 'No tiene registros de entrada',
                    'ultima_visita' => (empty($visitasHistoric[0]['fecha_entrada'])) ? '' : $visitasHistoric[0]['fecha_entrada'],
                    'expedicion'    => ModelGeneral::changeFormatDate($visitasHistoric[0]['expedicion_cedula']),
                    'correo'        => $visitasHistoric[0]['correo_personal'],
                    'status_io'     => false
                ];
            }
        }
    }

    public function CreateContratista()
    {
        try{
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Campos Requeridos'];
            else
            {
                $visitanteExist = ModelGeneral::recordExist([
                    'fields'    => "*",
                    'table'     => "sg_personal_proveedor",
                    'arguments' => "cedula_proveedor = '". $this->formData['cedula'] ."'"
                ]);

                if($visitanteExist)
                {
                    $hasVisita = ModelGeneral::recordExist([
                        'fields'    => "*",
                        'table'     => "sg_registros_mis_proveedores",
                        'arguments' => "id_sg_personal_proveedor = '". ModelGeneral::getIdContratistaByCedula($this->formData['cedula']) ."' AND id_sg_torre = '".$this->formData['idtorre']."' AND id_apto_oficina = '". $this->formData['idaptooficina'] ."' AND estado_salida = 0"
                    ]);
    
                    if($hasVisita)
                        return ['status' => false, 'message' => 'Este visitante cuenta con un registro, por favor dele salida'];
                    
                    $registraVisitaResidencial = Database::insert([
                        'table'     => "sg_registros_mis_proveedores",
                        'values'    => [
                            "id_sg_mi_proveedor"            => ModelGeneral::getIdProveedorByUid($this->formData['uid']),
                            "id_sg_personal_proveedor"      => ModelGeneral::getIdContratistaByCedula($this->formData['cedula']),
                            "id_sg_tipo_de_actividad"       => 1,
                            "id_sg_terminal_usuario"        => $this->formData['terminal'],
                            "id_sg_torre"                   => $this->formData['idtorre'],
                            "id_apto_oficina"               => $this->formData['idaptooficina'],
                            "fecha_entrada"                 => Database::dateTime(),
                            "fecha_salida"                  => _ERROR_NO_REGISTRA_SALIDA,
                            "estado_salida"                 => 0,
                            "estado"                        => ($this->formData['estado']) ? 2 : 1,
                            "photo"                         => 'photo',
                            "fecha_registro"                => Database::dateTime(),
                            "creado_por"                    => $this->formData['uid']
                        ],                    
                        'autoinc'   => true                    
                    ])->affectedRow();                    
    
                    if($registraVisitaResidencial)
                    {
                        Request::getRequest(NODE_SERVER . '/monitor/contratistas/' . $this->formData['uid']);
                        return ['status' => true, 'message' => 'Contratista registrado exitosamente'];
                    }
                    else
                        return ['status' => false, 'message' => 'Ha ocurrido un error al registrar el visitante'];
                }
                else
                {
                    $nombresContratista = ModelGeneral::removeWhiteSpaces($this->formData['nombres']);

                    $registraVisitante = Database::insert([
                        'table'     => "sg_personal_proveedor",
                        'values'    => [
                            "id_sg_mi_proveedor"    => ModelGeneral::getIdProveedorByUid($this->formData['uid']),
                            "id_sg_eps"             => $this->formData['eps'],
                            "id_sg_arl"             => $this->formData['arl'],
                            "cedula_proveedor"      => $this->formData['cedula'],
                            "nombres_proveedor"     => $nombresContratista,
                            "correo_proveedor"      => $this->formData['correo'],
                            "expedicion_cedula"     => $this->formData['expedicion'],
                            "estado"                => ($this->formData['estado']) ? 2 : 1,
                            "fecha_creacion"        => Database::dateTime(),                    
                            "creado_por"            => $this->formData['uid']
                        ],
                        'autoinc'  => true                    
                    ])->affectedRow();
    
                    $registraVisitaResidencial = Database::insert([
                        'table'     => "sg_registros_mis_proveedores",
                        'values'    => [
                            "id_sg_mi_proveedor"            => ModelGeneral::getIdProveedorByUid($this->formData['uid']),
                            "id_sg_personal_proveedor"      => ModelGeneral::getIdContratistaByCedula($this->formData['cedula']),
                            "id_sg_tipo_de_actividad"       => 1,
                            "id_sg_terminal_usuario"        => $this->formData['terminal'],
                            "id_sg_torre"                   => $this->formData['idtorre'],
                            "id_apto_oficina"               => $this->formData['idaptooficina'],
                            "fecha_entrada"                 => Database::dateTime(),
                            "fecha_salida"                  => _ERROR_NO_REGISTRA_SALIDA,
                            "estado_salida"                 => 0,
                            "estado"                        => ($this->formData['estado']) ? 2 : 1,
                            "photo"                         => 'photo',
                            "fecha_registro"                => Database::dateTime(),
                            "creado_por"                    => $this->formData['uid']
                        ],                    
                        'autoinc'   => true                    
                    ])->affectedRow();                    

                    if($registraVisitante && $registraVisitaResidencial)
                    {
                        Request::getRequest(NODE_SERVER . '/monitor/contratistas/' . $this->formData['uid']);
                        return ['status' => true, 'message' => 'Contratista registrado exitosamente'];
                    }
                    else
                        return ['status' => false, 'message' => 'Ha ocurrido un error al registrar el contratista'];
                }
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function UploadPhoto()
    {
        if(!isset($this->formData['photo']))
            return ['status' => false, 'message' => 'Photo is not defined'];
        else
        {
            if(empty(ModelGeneral::getIdPersonalByCedula($this->formData['cedula'])))
                ;            

            $existRecordMiPersonal = ModelGeneral::recordExist([
                'fields'    => "*",
                'table'     => "sg_registros_mi_personal",
                'arguments' => "id_sg_personal = '". ModelGeneral::getIdPersonalByCedula($this->formData['cedula']) ."' AND creado_por = '". $this->formData['uid'] ."'"
            ]);

            if($existRecordMiPersonal)
            {
                $updateRecord = Database::update([
                    'table'     => "sg_mi_personal",
                    'fields'    => [
                        'photo_personal' => $this->modelGeneral->uploadImage($this->formData['photo'])
                    ],
                    'arguments' => "cedula_personal = '". $this->formData['cedula'] ."' AND creado_por = '". $this->formData['uid'] ."' AND creado_por = '". $this->formData['uid'] ."'"
                ])->updateRow();

                if($updateRecord)
                    return ['status' => true, 'message' => 'Photo Uploaded Personal'];
                else
                    return ['status' => false, 'message' => 'not uploaded Personal'];
            }

            if(empty(ModelGeneral::getIdVisitanteByCedula($this->formData['cedula'])))
                ;

            $existRecordMisVisitantes = ModelGeneral::recordExist([
                'fields'    => "*",
                'table'     => "sg_registros_mis_visitantes",
                'arguments' => "id_sg_visitante = '". ModelGeneral::getIdVisitanteByCedula($this->formData['cedula']) ."' AND creado_por = '". $this->formData['uid'] ."'"
            ]);

            if($existRecordMisVisitantes)
            {
                $updateRecord = Database::update([
                    'table'     => "sg_registros_mis_visitantes",
                    'fields'    => [
                        'photo' => $this->modelGeneral->uploadImage($this->formData['photo'])
                    ],
                    'arguments' => "id_sg_visitante = '". ModelGeneral::getIdVisitanteByCedula($this->formData['cedula']) ."' AND creado_por = '". $this->formData['uid'] ."'"
                ])->updateRow();

                if($updateRecord)
                    return ['status' => true, 'message' => 'Photo Uploaded Visitante'];
                else
                    return ['status' => false, 'message' => 'not uploaded Visitante'];
            }

            if(empty(ModelGeneral::getIdContratistaByCedula($this->formData['cedula'])))
                ;

            $existRecordMisConstratistas = ModelGeneral::recordExist([
                'fields'    => "*",
                'table'     => "sg_registros_mis_proveedores",
                'arguments' => "id_sg_personal_proveedor = '". ModelGeneral::getIdContratistaByCedula($this->formData['cedula']) ."' AND creado_por = '". $this->formData['uid'] ."'"
            ]);

            if($existRecordMisConstratistas)
            {
                $updateRecord = Database::update([
                    'table'     => "sg_registros_mis_proveedores",
                    'fields'    => [
                        'photo' => $this->modelGeneral->uploadImage($this->formData['photo'])
                    ],
                    'arguments' => "id_sg_personal_proveedor = '". ModelGeneral::getIdContratistaByCedula($this->formData['cedula']) ."' AND creado_por = '". $this->formData['uid'] ."'"
                ])->updateRow();

                if($updateRecord)
                    return ['status' => true, 'message' => 'Photo Uploaded Contratista'];
                else
                    return ['status' => false, 'message' => 'not uploaded Contratista'];
            }
        }
    }

    public function Reload()
    {
        $userDataSession = Database::query([
            'fields'    => "stu.id_sg_terminal_usuario, se.nombre_empresa, ss.nombre_sede, se.id_sg_empresa, ss.id_sg_sede, stu.creado_por, stu.id_sg_estado, stu.creado_por, stu.id_sg_tipo_registro, stu.formulario, stu.id_sg_tipo_control",
            'table'     => "sg_terminales stu JOIN sg_empresas se ON stu.id_sg_empresa = se.id_sg_empresa JOIN sg_sedes ss ON stu.id_sg_sede = ss.id_sg_sede",
            'arguments' => "stu.creado_por = '". $this->formData['uid'] ."'"            
        ])->records()->resultToArray();

        if(ModelGeneral::hasRows($userDataSession))
        {
            foreach($userDataSession as $item => $value)
            {
                switch($value['id_sg_tipo_registro'])
                {
                    case 1:

                        if($value['id_sg_estado'] == 1)
                        {
                            $formPersonal = [
                                ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                    'status'    => true,
                                    'form'      => $value['formulario'],
                                    'arl'       => $this->modelGeneral->getArlList($value['creado_por']),
                                    'eps'       => $this->modelGeneral->getEpsList($value['creado_por']),
                                    'motivos'   => $this->modelGeneral->getMotivoList($value['creado_por']),
                                    'sede'      => (int) $value['id_sg_sede']
                                ]
                            ];
                        }
                        else
                        {
                            $formPersonal = [
                                ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                    'status'    => false,
                                    'form'      => ''
                                ]
                            ];
                        }
                    
                    break;

                    case 2:

                        if($value['id_sg_estado'] == 1)
                        {
                            if($value['id_sg_tipo_control'] == 1)
                            {                                        
                                $formVisitantes = [
                                    ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                        'status'    => true,
                                        'form'      => $value['formulario'],
                                        'torres_aptos'    => ModelTorres::ReadByIdEmpresa($value['id_sg_empresa']),                                                
                                        'sede'      => (int)$value['id_sg_sede']
                                    ]
                                ];
                            }
                            else
                            {
                                $formVisitantes = [
                                    ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                        'status'    => true,
                                        'form'      => $value['formulario'],
                                        'torres_oficinas'  => ModelOficinas::ReadByOwner($value['creado_por']),
                                        'sede'      => (int)$value['id_sg_sede']
                                    ]
                                ];
                            }                               
                        }
                        else
                        {
                            $formVisitantes = [
                                ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                    'status'    => false,
                                    'form'      => ''
                                ]
                            ];
                        }
                    break;

                    case 3:

                        if($value['id_sg_estado'] == 1)
                        {
                            $formContratistas = [
                                ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                    'status'        => true,
                                    'form'          => $value['formulario'],
                                    'arl'           => $this->modelGeneral->getArlList($value['creado_por']),
                                    'eps'           => $this->modelGeneral->getEpsList($value['creado_por']),
                                    'actividades'   => $this->modelGeneral->getActividadesList($value['creado_por']),
                                    'empresas'      => $this->modelGeneral->getEmpresasList($value['creado_por']),
                                    'sede'          => (int)$value['id_sg_sede']
                                    
                                ]
                            ];
                        }
                        else 
                        {
                            $formContratistas = [
                                ModelGeneral::setTipoRegistro($value['id_sg_tipo_registro']) => [
                                    'status'    => false,
                                    'form'      => ''
                                ]
                            ];
                        }
                        
                    break;
                }
            }

            if(empty($formPersonal['personal']))
                $formPersonal['personal'] = ['status' => false, 'form' => ''];

            if(empty($formVisitantes['visitantes']))
                $formPersonal['visitantes'] = ['status' => false, 'form' => ''];

            if(empty($formContratistas['contratistas']))
                $formPersonal['contratistas'] = ['status' => false, 'form' => ''];
            
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
                    'personal'      => $formPersonal['personal'],
                    'visitantes'    => $formVisitantes['visitantes'],
                    'contratistas'  => $formContratistas['contratistas']
                ]
            ];
        }
    }
}