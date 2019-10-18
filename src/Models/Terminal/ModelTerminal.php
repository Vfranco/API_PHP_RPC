<?php

namespace Models\Terminal;

use Database\Database;
use Core\{Validate};
use Models\Apartamentos\ModelApartamentos;
use Models\General\ModelGeneral;
use Models\Empleados\ModelEmpleados;
use Models\Oficinas\ModelOficinas;
use Models\Torres\ModelTorres;

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

    /**
     * 
     */
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
                                                'torres'    => ModelTorres::ReadByIdEmpresa($value['id_sg_empresa']),                                                
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
                                                'oficinas'  => ModelOficinas::ReadByOwner($value['creado_por']),
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
                                            'status'    => true,
                                            'form'      => $value['formulario'],
                                            'arl'       => $this->modelGeneral->getArlList($value['creado_por']),
                                            'eps'       => $this->modelGeneral->getEpsList($value['creado_por']),
                                            'sede'      => (int)$value['id_sg_sede']
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

    /**
     * 
     */
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

    /**
     * 
     */
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

    /**
     * 
     */
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

    /**
     * 
     */
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

    /**
     * 
     */
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
                                        'torres'    => ModelTorres::ReadByIdEmpresa($value['id_sg_empresa']),
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
                                        'oficinas'  => ModelOficinas::ReadByOwner($value['creado_por']),
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
                                    'status'    => true,
                                    'form'      => $value['formulario'],
                                    'arl'       => $this->modelGeneral->getArlList($value['creado_por']),
                                    'eps'       => $this->modelGeneral->getEpsList($value['creado_por']),
                                    'sede'      => (int)$value['id_sg_sede']
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
    /**
     * 
     */
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
                            'photo'                     => $this->modelGeneral->uploadImage($this->formData['photo']),
                            'creado_por'                => $this->formData['uid']
                        ],
                        'autoinc'  => true                    
                    ])->affectedRow();
    
                    if($registraVisitaResidencial)
                        return ['status' => true, 'message' => 'Visitante registrado exitosamente'];
                    else
                        return ['status' => false, 'message' => 'Ha ocurrido un error al registrar el visitante'];
                }
                else
                {
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
                            'photo'                     => $this->modelGeneral->uploadImage($this->formData['photo']),
                            'creado_por'                => $this->formData['uid']
                        ],
                        'autoinc'  => true                    
                    ])->affectedRow();

                    if($registraVisitante && $registraVisitaResidencial)
                        return ['status' => true, 'message' => 'Visitante registrado exitosamente'];
                    else
                        return ['status' => false, 'message' => 'Ha ocurrido un error al registrar el visitante'];
                }
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 
     */
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
                'arguments' => "srmv.id_sg_visitante = '". ModelGeneral::getIdVisitanteByCedula($this->formData['cedula']) ."' ORDER BY fecha_registro DESC"
            ])->records()->resultToArray();

            if(!ModelGeneral::hasRows($visitasHistoric))            
                return ['status' => true, 'message'   => 'No existen registros'];
            else
            {
                return [
                    'status'        => true, 
                    'visitante'     => $visitasHistoric[0]['nombres_visitante'],
                    'ultima_visita' => (empty($visitasHistoric[0]['fecha_visita'])) ? '' : $visitasHistoric[0]['fecha_visita']
                ];
            }
        }
    }

    /**
     * 
     */
    public function RegistraVisitaResidencial()
    {
        try{
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Campos Requeridos'];
            else
            {
                $hasVisita = ModelGeneral::recordExist([
                    'fields'    => "*",
                    'table'     => "sg_registros_mis_visitantes",
                    'arguments' => "id_sg_visitante = '". $this->formData['id_visitante'] ."' AND id_sg_torre = '".$this->formData['idtorre']."' AND id_sg_apto = '". $this->formData['idaptooficina'] ."' AND estado_salida = 0"
                ]);

                if($hasVisita)
                    return ['status' => false, 'message' => 'Este visitante cuenta con un registro, por favor dele salida'];

                $registraVisitaResidencial = Database::insert([
                    'table'     => "sg_registros_mis_visitantes",
                    'values'    => [
                        'id_sg_visitante'           => (int) $this->formData['id_visitante'],
                        'id_sg_terminal_usuario'    => $this->formData['terminal'],
                        'id_sg_tipo_control'        => $this->formData['tipo_control'],
                        'id_sg_torre'               => $this->formData['idtorre'],
                        'id_sg_apto'                => $this->formData['idaptooficina'],
                        'fecha_visita'              => Database::dateTime(),
                        'salida_visita'             => _ERROR_NO_REGISTRA_SALIDA,
                        'estado_salida'             => 0,
                        'fecha_registro'            => Database::dateTime(),
                        'photo'                     => $this->modelGeneral->uploadImage($this->formData['photo']),
                        'creado_por'                => $this->formData['uid']
                    ],
                    'autoinc'  => true                    
                ])->affectedRow();

                if($registraVisitaResidencial)
                    return ['status' => true, 'message' => 'Visita Registrada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al registrar la visita'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 
     */
    public function RegistraSalidaVisitante()
    {
        try{
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Campos Requeridos'];
            else
            {
                $idVisitante = ModelGeneral::getIdVisitanteByCedula($this->formData['cedula']);

                $hasSalidas = ModelGeneral::recordExist([
                    'fields'    => "*",
                    'table'     => "sg_registros_mis_visitantes",
                    'arguments' => "id_sg_visitante = '". $idVisitante ."' AND id_sg_torre = '".$this->formData['torre']."' AND id_sg_apto = '". $this->formData['aptoficina'] ."' AND estado_salida = 0"
                ]);

                if(!$hasSalidas)
                    return ['status' => false, 'message' => 'Este visitante no tiene registros de entrada'];

                $salida = Database::update([
                    'table'      => "sg_registros_mis_visitantes",
                    'fields'      => [
                        'salida_visita'  => Database::dateTime(),
                        'estado_salida'  => 1
                    ],
                    'arguments' => "id_sg_visitante = '". $idVisitante ."' AND id_sg_torre = '".$this->formData['torre']."' AND id_sg_apto = '". $this->formData['aptoficina'] ."'"
                ])->updateRow();

                if($salida)
                    return ['status' => true, 'message' => "Salida Registrada Exitosamente", 'salida' => Database::dateTime()];
                else
                    return ['status' => false, 'message' => 'No se logro actualizar la salida'];
            }
        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function RegistraVisitanteOficina()
    {
        
    }
}