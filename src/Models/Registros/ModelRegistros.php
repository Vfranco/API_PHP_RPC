<?php

namespace Models\Registros;

use Database\Database;
use Models\General\ModelGeneral;
use Core\{ActionFilters, Validate};

class ModelRegistros extends ModelGeneral
{
    private $formData;
    
    public function __construct($formData)
    {
        $this->formData = $formData;
        return $this;
    }

    public function CheckRegistroPersonal()
    {
        try {
            if (!Validate::notEmptyFields($this->formData))
                return $this->processRegistroPersonal($this->formData);
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function processRegistroPersonal($formData)
    {
        $checkPersonal = Database::query([
            'fields'    => "*",
            'table'     => "cms_registro_personal crp JOIN cms_empresas ce ON crp.cms_empresa_id_cms_empresa = ce.id_cms_empresas",
            'arguments' => "ce.id_cms_empresas = '".Database::escapeSql($formData['idCmsEmpresa'])."' AND crp.cedula_registro = '". Database::escapeSql($formData['cedula']) ."'"            
        ])->records()->resultToArray();

        if(isset($checkPersonal[0]['empty']) && $checkPersonal[0]['empty'] == true)
            return ['status' => false, 'message' => 'El usuario no se encuentra registrado'];
        
        $getPersonal = Database::query([
            'fields'    => "cedula_registro as cedula, CONCAT(nombres_registro, ' ', apellidos_registros) as fullName, cms_estados_id_cms_estados as estado",
            'table'     => "cms_registro_personal",
            'arguments' => "cedula_registro = '". Database::escapeSql($formData['cedula']) ."' AND cms_estados_id_cms_estados = 1"
        ])->records()->resultToArray();

        if(isset($getPersonal[0]['empty']) && $getPersonal[0]['empty'] == true)
            return ['status' => false, 'message' => 'El usuario no se encuentra registrado'];

        return [
            'status'    => true, 
            'message'   => 'Esta registrado',
            'persona'   => [
                'cedula'    => (int) $getPersonal[0]['cedula'],
                'fullName'  => $getPersonal[0]['fullName'],
                'estado'    => (int) $getPersonal[0]['estado']
            ]
        ];
    }

    public function RegistroActividad()
    {
        if(empty($this->getIdZonaBySede($this->formData['idSede'])))
            return ['status' => false, 'message' => 'No hay una sede relacionada para este Usuario'];
        else if (empty($this->getIdActividadById($this->formData['idActividad'])))
            return ['status' => false, 'message' => 'Esta Actividad no se encuentra registrada'];
        else if(empty($this->getIdUser($this->formData['idAclUser'])))
            return ['status' => false, 'message' => 'El usuario no se encuentra registrado'];
        else if(empty($this->getPersonalById($this->formData['idPersonal'])))
            return ['status' => false, 'message' => 'La persona no se encuentra registrada'];
        else
        {
            if($this->checkIfTokenExist($this->formData['tokenNovelty']))
            {
                $updateActividad = Database::update([
                    'table'     => "cms_registro_actividad",
                    'fields'    => [
                        'fecha_salida'  => $this->formData['salida']
                    ],
                    'arguments' => "token = '". Database::escapeSql($this->formData['tokenNovelty']) ."'"
                ])->updateRow();

                if($updateActividad)
                    return ['status' => true, 'message' => 'Registro Actualizado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error'];
            }
            else 
            {
                $saveActividad = Database::insert([
                    'table'     => "cms_registro_actividad",
                    'values'    => [
                        'cms_empresa_id_empresa'                    => $this->formData['idEmpresa'],
                        'cms_sedes_id_cms_sede'                     => $this->getIdZonaBySede($this->formData['idSede']),
                        'cms_estados_id_cms_estdos'                 => 1,
                        'tipo_actividades_id_tipo_actividad'        => $this->getIdActividadById($this->formData['idActividad']),                    
                        'cms_registros_id_registro'                 => $this->getPersonalById($this->formData['idPersonal']),                   
                        'tarea_a_realizar'                          => $this->formData['tarea'],
                        'fecha_ingreso'                             => $this->formData['ingreso'],
                        'fecha_salida'                              => $this->formData['salida'],
                        'marca_equipo'                              => $this->formData['equipo'],
                        'serial'                                    => (ActionFilters::dontApply($this->formData['serial'])) ? 'N/A' : $this->formData['serial'],
                        'arl'                                       => (ActionFilters::dontApply($this->formData['arl'])) ? 'N/A' : $this->formData['arl'],
                        'eps'                                       => (ActionFilters::dontApply($this->formData['eps'])) ? 'N/A' : $this->formData['eps'],
                        'c1'                                        => (ActionFilters::dontApply($this->formData['c1'])) ? 'N/A' : $this->formData['c1'],
                        'c2'                                        => (ActionFilters::dontApply($this->formData['c2'])) ? 'N/A' : $this->formData['c2'],
                        'c3'                                        => (ActionFilters::dontApply($this->formData['c3'])) ? 'N/A' : $this->formData['c3'],
                        'id_acl_user'                               => $this->formData['idAclUser'],
                        'photo'                                     => $this->uploadImage($this->formData['photo']),
                        'token'                                     => $this->formData['tokenNovelty']
                    ],
                    'autoinc'   => true                
                ])->affectedRow();
        
                if($saveActividad)
                    return ['status' => true, 'message' => 'Registro guardado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error'];
            }
        }
    }

    public function ObtenerResumenVisitas()
    {
        if(isset($this->formData['id_cms_empresa']))
        {
            $visitas = Database::query([
                'fields'    => "smp.cedula_personal, smp.nombres_personal, smp.apellidos_personal, (SELECT nombre_sede FROM sg_sedes WHERE id_sg_sede = smp.id_sg_sede) as nombre_sede, (SELECT nombre_sede	FROM sg_sedes WHERE	id_sg_sede = srmp.id_sg_sede_visitada) as sede_visitada, srmp.fecha_ingreso, srmp.fecha_salida, sa.nombre_arl, se.nombre_eps",
                'table'     => "sg_registros_mi_personal srmp JOIN sg_mi_personal smp ON srmp.`id_sg_personal` = smp.`id_sg_personal` JOIN sg_arl sa ON smp.id_sg_arl = sa.id_sg_arl JOIN sg_eps se ON smp.id_sg_eps = se.id_sg_eps",
                'arguments' => "srmp.id_sg_empresa = '". ModelGeneral::getIdEmpresaByUser($this->formData['id_cms_empresa']) ."' ORDER BY srmp.id_sg_registro DESC"
            ])->records()->resultToArray();
        }
        else 
        {
            $visitas = Database::query([
                'fields'    => "*",
                'table'     => "sg_registros_mi_personal srmp JOIN sg_mi_personal smp ON srmp.`id_sg_personal` = smp.`id_sg_personal`",
                'arguments' => "srmp.id_sg_personal = '". $this->formData['id_cms_empleado'] ."' ORDER BY srmp.id_sg_registro DESC"
            ])->records()->resultToArray();
        }        

        if(isset($visitas[0]['empty']) && $visitas[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos'];

        return [
            'status'    => true,
            'rows'      => $visitas
        ];
    }

    public function ObtenerReporteActividades()
    {
        $arguments = "";
        $isDiferentField = false;
        
        foreach($this->formData as $field => $value)
        {
            if($field == 'empleado')
            {
                if($value == '*')                
                    $arguments .= "";                
                else 
                {
                    if($isDiferentField)
                        $arguments .= " AND";

                    $arguments .= " smp.id_sg_personal = '". $value ."'";
                    $isDiferentField = true;
                }
            }

            if($field == 'sede')
            {
                if($value == '*')
                    $arguments .= "";
                else
                {
                    if($isDiferentField)
                        $arguments .= " AND";

                    $arguments .= " smp.id_sg_sede = '". $value ."'";
                    $isDiferentField = true;
                }                
            }

            if($field == 'entradaSalida')
            {
                if($value == '*')
                    $arguments .= " AND smp.fecha_ingreso BETWEEN '". $this->formData['desde'] ." 00:00:00' AND '". $this->formData['hasta'] ." 23:59:59' AND smp.fecha_salida BETWEEN '". $this->formData['desde'] ." 00:00:00' AND '". $this->formData['hasta'] ." 23:59:59'";
                else
                {
                    if($isDiferentField)
                        $arguments .= " AND";

                    $arguments .= " $value BETWEEN '". $this->formData['desde'] ." 00:00:00' AND '". $this->formData['hasta'] ." 23:59:59'";
                    $isDiferentField = true;
                }
            }

            if($field == 'id_cms_empresa')
            {
                if($value == '*')
                    $arguments .= "";
                else
                {
                    if($isDiferentField)
                        $arguments .= " AND";

                    $arguments .= " smp.id_sg_empresa = '". ModelGeneral::getIdEmpresaByUser($this->formData['id_cms_empresa']) ."'";
                    $isDiferentField = true;
                }
            }            
        }

        $visitas = Database::query([
            'fields'    => $this->formData['fields'],
            'table'     => "sg_mi_personal sg JOIN sg_registros_mi_personal smp ON sg.`id_sg_personal` = smp.`id_sg_personal` JOIN sg_sedes ss ON sg.`id_sg_sede` = ss.`id_sg_sede`",
            'arguments' => $arguments            
        ])->records()->resultToArray();

        if(isset($visitas[0]['empty']) && $visitas[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos'];

        return [
            'status'    => true,
            'rows'      => $visitas
        ];
    }

    public function ExportExcel()
    {
        if(isset($this->formData['id_cms_empresa']))
        {
            $id_cms_empresa = ModelGeneral::getIdEmpresaByUser($this->formData['id_cms_empresa']);            

            $visitas = Database::query([
                'fields'    => "sg.`cedula_personal` AS Cedula, sg.`nombres_personal` AS Nombres, sg.`apellidos_personal` AS Apellidos, ss.`nombre_sede` AS Sede, smp.`fecha_ingreso` AS Entrada, smp.`fecha_salida` AS Salida",
                'table'     => "sg_mi_personal sg JOIN sg_registros_mi_personal smp ON sg.`id_sg_personal` = smp.`id_sg_personal` JOIN sg_sedes ss ON sg.`id_sg_sede` = ss.`id_sg_sede`",
                'arguments' => "sg.`id_sg_empresa` = '". $id_cms_empresa ."' ORDER BY smp.`id_sg_registro` DESC"
            ])->records()->resultToArray();
        }        

        if(isset($visitas[0]['empty']) && $visitas[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos'];

        return [
            'status'    => true,
            'rows'      => $visitas
        ];
    }
}