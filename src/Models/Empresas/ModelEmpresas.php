<?php

namespace Models\Empresas;

use Database\Database;
use Core\{Validate, Token};
use Models\General\ModelGeneral;
use Models\Authentication\ModelAuthentication;

class ModelEmpresas
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
                $empresaExist = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_empresas",
                    'arguments'  => "nit_empresa = '". $this->formData['nitEmpresa'] ."'"
                ]);

                if($empresaExist)
                    return ['status' => false, 'message' => "La empresa ya se encuentra registrada"];

                $saveEmpresa = Database::insert([
                    'table'     => 'sg_empresas',
                    'values'    => [                        
                        "id_sg_usuario"         => ModelGeneral::getIdUserByDecode($this->formData['usuario']),
                        "id_sg_estado"	        => 1,
                        "id_sg_tipo_registro"   => $this->formData['tiporegistro'],
                        "nit_empresa"		    => $this->formData['nitEmpresa'],
                        "nombre_empresa"	    => $this->formData['nombreEmpresa'],                        
                        "correo_empresa"	    => $this->formData['emailEmpresa'],
                        "direccion_empresa"     => $this->formData['dirEmpresa'],
                        "telefono_celular"      => $this->formData['celularEmpresa'],
                        'fecha_registro'        => Database::dateTime()
                    ],
                    'autoinc'   => true                    
                ])->affectedRow();

                if($saveEmpresa)
                    return ['status' => true, 'message' => 'Empresa registrada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear la empresa'];
            }

        } catch(\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function Read()
    {
        $resultSet = Database::query([
            'fields'    => "id_cms_empresas, nombre_empresa as empresa, nit_empresa as nit, email_empresa as email, dir_empresa as direccion, max_dispositivos as dispositivos, cms_estados_id_cms_estados as estado",
            'table'     => "cms_empresas",
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos en la Empresa'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByUser()
    {
        $resultSet = Database::query([
            'fields'    => "id_sg_empresa, nombre_empresa as empresa, nit_empresa as nit, correo_empresa as email, direccion_empresa as direccion, id_sg_estado as estado",
            'table'     => "sg_empresas",
            'arguments' => "id_sg_usuario = '". ModelGeneral::getIdUserByDecode($this->formData['uid']) ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos en la Empresa'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadById()
    {
        $resultSet = Database::query([
            'fields'    => "id_sg_empresa, nombre_empresa as empresa, nit_empresa as nit, correo_empresa as email, direccion_empresa as direccion, id_sg_estado as estado, telefono_celular",
            'table'     => "sg_empresas",
            'arguments' => "id_sg_empresa = '". ModelGeneral::getIdEmpresaByUser($this->formData['id_cms_empresas']) ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos en la Empresa'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByNit()
    {
        $resultSet = Database::query([
            'fields'    => "id_cms_empresas, nombre_empresa as empresa, nit_empresa as nit, email_empresa as email, dir_empresa as direccion, max_dispositivos as dispositivos, cms_estados_id_cms_estados as estado",
            'table'     => "cms_empresas",
            'arguments' => "nit_empresa = '". $this->formData['nitEmpresa'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos en la Empresa'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByAll()
    {
        $resultSet = Database::query([
            'fields'    => "id_cms_empresas, nombre_empresa as empresa, nit_empresa as nit, email_empresa as email, dir_empresa as direccion, max_dispositivos as dispositivos, cms_estados_id_cms_estados as estado",
            'table'     => "cms_empresas",
            'arguments' => $this->formData['argument']            
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos en la Empresa'];
        
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
                $updateArticulo = Database::update([
                    'table'     => "sg_empresas",
                    'fields'    => [                        
                        "nit_empresa"		    => $this->formData['nitEmpresa'],
                        "nombre_empresa"	    => $this->formData['nombreEmpresa'],                        
                        "correo_empresa"	    => $this->formData['emailEmpresa'],
                        "direccion_empresa"     => $this->formData['dirEmpresa']                        
                    ],
                    'arguments' => "id_sg_empresa = '". $this->formData['id_cms_empresas'] ."'"
                ])->updateRow();

                if($updateArticulo)
                    return ['status' => true, 'message' => 'Empresa Actualizada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la Empresa'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function Disable()
    {
        try
        {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $updateArticulo = Database::update([
                    'table'     => "cms_empresas",
                    'fields'    => [                        
                        "cms_estados_id_cms_estados"	=> $this->formData['estado']
                    ],
                    'arguments' => "id_cms_empresas = '". $this->formData['id_cms_empresas'] ."'"
                ])->updateRow();

                if($updateArticulo)
                    return ['status' => true, 'message' => 'Empresa Deshabilitada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al deshabilitar el Documento'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public static function getEmpresasList()
    {
        $getEmpresas = Database::query([
            'fields'    => "id_cms_empresas, nombre_empresa, cms_estados_id_cms_estados as estado",
            'table'     => "cms_empresas",            
        ])->records()->resultToArray();

        if(isset($getEmpresas[0]['empty']) && $getEmpresas[0]['empty'] == true)
            return [];

        $result = [];

        foreach($getEmpresas as $key => $value)
        {
            if($getEmpresas[$key]['estado'] != _ID_ESTADO_INACTIVO)
            {
                if($getEmpresas[$key]['nombre_empresa'] === _OTRA_ACTIVIDAD)
                {
                    $result[] = [
                        'idEmpresa'        => 1000,
                        'nombreEmpresa'    => $getEmpresas[$key]['nombre_empresa']
                    ];   
                }
                else
                {
                    $result[] = [
                        'idEmpresa'        => (int) $getEmpresas[$key]['id_cms_empresas'],
                        'nombreEmpresa'    => $getEmpresas[$key]['nombre_empresa']
                    ];
                }                
            }            
        }

        return $result;
    }

    public function GetConfigForm()
    {
        $auth = new ModelAuthentication($this->formData);
        
        return [
            'status'      => true,
            'formularios'  => [
                'Control_de_Personal'       => $auth->getFormIdEmpresaSede($this->formData['idEmpresa'])[0],
                'Control_de_Visitas'        => $auth->getFormIdEmpresaSede($this->formData['idEmpresa'])[1],
                'Control_de_Proveedores'    => $auth->getFormIdEmpresaSede($this->formData['idEmpresa'])[2]
            ],
            'personal'    => $auth->getPersonalRegistrado($this->formData['idEmpresa']),
            'actividades' => $auth->getActividades($this->formData['idEmpresa']),
            'empresas'      => ModelEmpresas::getEmpresasList(),
            'equipos'       => $auth->getEquiposList(),
            'arl'           => $auth->getArlList(),
            'eps'           => $auth->getEpsList(),
            'autorizados'   => $auth->getAutorizadoList()
        ];
    }    
}