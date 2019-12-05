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
                    'arguments' => "id_sg_terminal = '". ModelGeneral::getTerminalIdByUserName($this->formData['usuario']) ."'"
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
                        'id_sg_terminal'    => $userTerminal,
                        'direccion_ip'      => _REMOTE_ADDR_GENERAL,
                        'fecha_conexion'    => Database::dateTime()
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
                            'terminal'      => (int) $userDataSession[0]['id_sg_terminal_usuario'],
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
                'arguments' => "id_sg_personal = '". ModelGeneral::getIdPersonalByCedula($this->formData['cedula']) ."' AND fecha_salida = '0000-00-00 00:00:00' AND estado_salida = '0'"
            ]);

            if($hasSalidas)
            {
                $salida = Database::update([
                    'table'      => "sg_registros_mi_personal",
                    'fields'      => [
                        'fecha_salida'      => Database::dateTime(),
                        'estado_salida'     => 1
                    ],
                    'arguments' => "id_sg_personal = '". ModelGeneral::getIdPersonalByCedula($this->formData['cedula']) ."' AND fecha_salida = '0000-00-00 00:00:00' AND estado_salida = '0'"
                ])->updateRow();

                if($salida)
                {
                    //Request::getRequest(NODE_SERVER . '/monitor/personal?uid=' . $this->formData['uid']);

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
                //Request::getRequest(NODE_SERVER . '/monitor/personal?uid=' . $this->formData['uid']);

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
                return ['status' => false, 'message' => 'Campos Requeridos'];
            else
            {
                $personalExist = ModelGeneral::recordExist([
                    'fields'    => "*",
                    'table'     => "sg_mi_personal",
                    'arguments' => "cedula_personal = '". $this->formData['cedula'] ."'"
                ]);

                if($personalExist)
                {
                    $hasVisita = ModelGeneral::recordExist([
                        'fields'    => "*",
                        'table'     => "sg_registros_mi_personal",
                        'arguments' => "id_sg_personal = '". ModelGeneral::getIdPersonalByCedula($this->formData['cedula']) ."' AND fecha_salida = '". _ERROR_NO_REGISTRA_SALIDA ."' AND estado_salida = 0"
                    ]);
    
                    if($hasVisita)
                        return ['status' => false, 'message' => 'Este personal cuenta con un registro, por favor dele salida'];

                    $lastIdPersonal = Database::query([
                        'fields'    => "id_sg_personal",
                        'table'     => "sg_mi_personal",
                        'arguments' => "cedula_personal = '". $this->formData['cedula'] ."'"
                    ])->records()->resultToArray();
                    
                    $registraVisitaResidencial = Database::insert([
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
                        'autoinc'  => true                    
                    ])->affectedRow();

                    //Request::getRequest(NODE_SERVER . '/monitor/personal?uid=' . $this->formData['idEmpresa']);
    
                    if($registraVisitaResidencial)
                        return ['status' => true, 'message' => 'Personal registrado exitosamente'];
                    else
                        return ['status' => false, 'message' => 'Ha ocurrido un error al registrar el visitante'];
                }
                else
                {
                    $hasVisita = ModelGeneral::recordExist([
                        'fields'    => "*",
                        'table'     => "sg_registros_mi_personal",
                        'arguments' => "id_sg_personal = '". ModelGeneral::getIdPersonalByCedula($this->formData['cedula']) ."' AND fecha_salida = '". _ERROR_NO_REGISTRA_SALIDA ."' AND estado_salida = 0"
                    ]);
    
                    if($hasVisita)
                        return ['status' => false, 'message' => 'Este visitante cuenta con un registro, por favor dele salida'];

                    $saveEmpleado = Database::insert([
                        'table'     => 'sg_mi_personal',
                        'values'    => [
                            "id_sg_empresa"         => ModelGeneral::getIdEmpresaByUser($this->formData['idEmpresa']),
                            "id_sg_sede"            => $this->formData['sede'],
                            "id_sg_estado"          => 1,
                            "id_sg_arl"             => $this->formData['arl'],
                            "id_sg_eps"             => $this->formData['eps'],
                            "cedula_personal"       => $this->formData['cedula'],
                            "nombres_personal"	    => $this->formData['nombres'],
                            "apellidos_personal"	=> $this->formData['apellidos'],
                            "direccion_personal"    => $this->formData['direccion'],
                            "telfono_personal"      => $this->formData['telefono'],
                            "correo_personal"       => $this->formData['email'],
                            "photo_personal"        => (!isset($this->formData['photo'])) ? '0' : $this->modelGeneral->uploadImage($this->formData['photo']),
                            "fecha_registro"        => Database::dateTime(),
                            "creado_por"            => $this->formData['idEmpresa']
                        ],
                        'autoinc'   => true                    
                    ])->affectedRow();
    
                    $lastIdVisitante = Database::query([
                        'fields'    => "id_sg_personal",
                        'table'     => "sg_mi_personal",
                        'arguments' => "cedula_personal = '". $this->formData['cedula'] ."'"
                    ])->records()->resultToArray();
                    
                    $registraVisitaResidencial = Database::insert([
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
                            'creado_por'        => $this->formData['idEmpresa']
                        ],
                        'autoinc'  => true                    
                    ])->affectedRow();

                    //Request::getRequest(NODE_SERVER . '/monitor/personal?uid=' . $this->formData['idEmpresa']);

                    if($registraVisitaResidencial && $saveEmpleado)
                        return ['status' => true, 'message' => 'Personal registrado exitosamente'];
                    else
                        return ['status' => false, 'message' => 'Ha ocurrido un error al registrar el personal'];
                }
            }

        } catch(\Exception $e){
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
                    //Request::getRequest(NODE_SERVER . '/monitor/visitantes?uid=' . $this->formData['uid']);

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
                //Request::getRequest(NODE_SERVER . '/monitor/visitantes?uid=' . $this->formData['uid']);

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

                    //Request::getRequest(NODE_SERVER . '/monitor/visitantes?uid=' . $this->formData['uid']);
    
                    if($registraVisitaResidencial)
                        return ['status' => true, 'message' => 'Visitante registrado exitosamente'];
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

                    //Request::getRequest(NODE_SERVER . '/monitor/visitantes?uid=' . $this->formData['uid']);

                    if($registraVisitante && $registraVisitaResidencial)
                        return ['status' => true, 'message' => 'Visitante registrado exitosamente here'];
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
                return [
                    'status'        => true,
                    'contratista'   => '',
                    'message'       => 'No tiene registros de entrada',
                    'ultima_visita' => (empty($visitasHistoric[0]['fecha_entrada'])) ? '' : $visitasHistoric[0]['fecha_entrada']
                ];
            }

            $hasSalidas = ModelGeneral::recordExist([
                'fields'    => "*",
                'table'     => "sg_registros_mis_proveedores",
                'arguments' => "id_sg_personal_proveedor = '". ModelGeneral::getIdContratistaByCedula($this->formData['cedula']) ."' AND fecha_salida = '0000-00-00 00:00:00' AND estado_salida = '0'"
            ]);

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

                if($salida)
                {
                    //Request::getRequest(NODE_SERVER . '/monitor/contratistas?uid=' . $this->formData['uid']);

                    return [
                        'status'        => true,
                        'contratista'   => '',
                        'message'       => 'Gracias ' . $visitasHistoric[0]['nombres_personal'],
                        'ultima_visita' => (empty($visitasHistoric[0]['fecha_entrada'])) ? '' : $visitasHistoric[0]['fecha_entrada']
                    ];
                }
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la salida'];
            }
            else
            {
                //Request::getRequest(NODE_SERVER . '/monitor/contratistas?uid=' . $this->formData['uid']);

                return [
                    'status'        => true,
                    'contratista'   => $visitasHistoric[0]['nombres_personal'],
                    'message'       => 'No tiene registros de entrada',
                    'ultima_visita' => (empty($visitasHistoric[0]['fecha_entrada'])) ? '' : $visitasHistoric[0]['fecha_entrada']
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
                            "estado"                        => ($this->formData['estado']) ? 1 : 2,
                            "photo"                         => 'photo',
                            "fecha_registro"                => Database::dateTime(),
                            "creado_por"                    => $this->formData['uid']
                        ],                    
                        'autoinc'   => true                    
                    ])->affectedRow();

                    //Request::getRequest(NODE_SERVER . '/monitor/personal?uid=' . $this->formData['uid']);
    
                    if($registraVisitaResidencial)
                        return ['status' => true, 'message' => 'Contratista registrado exitosamente'];
                    else
                        return ['status' => false, 'message' => 'Ha ocurrido un error al registrar el visitante'];
                }
                else
                {
                    $registraVisitante = Database::insert([
                        'table'     => "sg_personal_proveedor",
                        'values'    => [
                            "id_sg_mi_proveedor"    => ModelGeneral::getIdProveedorByUid($this->formData['uid']),
                            "id_sg_eps"             => $this->formData['eps'],
                            "id_sg_arl"             => $this->formData['arl'],
                            "cedula_proveedor"      => $this->formData['cedula'],
                            "nombres_proveedor"     => $this->formData['nombres'],                    
                            "correo_proveedor"      => $this->formData['correo'],
                            "expedicion_cedula"     => $this->formData['expedicion'],
                            "estado"                => ($this->formData['estado']) ? 1 : 2,
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
                            "estado"                        => ($this->formData['estado']) ? 1 : 2,
                            "photo"                         => 'photo',
                            "fecha_registro"                => Database::dateTime(),
                            "creado_por"                    => $this->formData['uid']
                        ],                    
                        'autoinc'   => true                    
                    ])->affectedRow();

                    //Request::getRequest(NODE_SERVER . '/monitor/personal?uid=' . $this->formData['uid']);

                    if($registraVisitante && $registraVisitaResidencial)
                        return ['status' => true, 'message' => 'Contratista registrado exitosamente'];
                    else
                        return ['status' => false, 'message' => 'Ha ocurrido un error al registrar el visitante'];
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
            $updateRecord = Database::update([
                'table'     => "sg_mi_personal",
                'fields'    => [
                    'photo_personal' => $this->modelGeneral->uploadImage($this->formData['photo'])
                ],
                'arguments' => "cedula_personal = '". $this->formData['cedula'] ."'"
            ])->updateRow();

            if($updateRecord)
                return ['status' => true, 'message' => 'Photo Uploaded'];
            else
                return ['status' => false, 'message' => 'not uploaded'];
        }
    }
}