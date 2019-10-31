<?php

namespace Models\UsuariosTerminal;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;
use Exception;

class ModelUsuariosTerminal
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

    public function CreateUserTerminal()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Todos los campos son obligatorios'];
            else
            {                
                $hasUserTerminal = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_terminal_usuarios",
                    'arguments'  => "usuario = '". $this->formData['nombreUsuario'] ."' AND creado_por = '". $this->formData['uid'] ."'"
                ]);

                if($hasUserTerminal)
                    return ['status' => false, 'message' => 'Ya se encuentra registrado este usuario'];

                $hasSede = ModelGeneral::recordExist([
                    'fields'     => "*",
                    'table'      => "sg_terminales",
                    'arguments'  => "id_sg_sede = '". $this->formData['sede'] ."'"
                ]);

                if($hasSede)
                    return ['status' => false, 'message' => 'Ya existe una terminal asignada en esta Sede'];
                    
                $registraUsuarioTerminal = Database::insert([
                    'table'     => "sg_terminal_usuarios",
                    'values'    => [
                        'id_sg_empresa'     => ModelGeneral::getIdEmpresaByUser($this->formData['uid']),
                        'id_sg_estado'      => ($this->formData['status']) ? 1 : 2,
                        'usuario'           => $this->formData['nombreUsuario'],
                        'password'          => md5($this->formData['confirm']),
                        'recovery'          => $this->formData['confirm'],
                        'fecha_registro'    => Database::dateTime(),
                        'creado_por'        => $this->formData['uid']
                    ],
                    'autoinc'   => true
                ])->affectedRow();

                $getLastInsert = Database::query([
                    'fields'    => "id_sg_terminal_usuario",
                    'table'     => "sg_terminal_usuarios",
                    'arguments' => "creado_por = '". $this->formData['uid'] ."' ORDER BY fecha_registro DESC"
                ])->assoc('id_sg_terminal_usuario');

                $formulario = [
                    'c1'    => [
                        'type'          => 'text',
                        'required'      => false,
                        'placeholder'   => '',
                        'show'          => true
                    ],                    
                    'photo'             => $this->formData['photo'],
                    'eps'               => $this->formData['eps'],
                    'arl'               => $this->formData['arl'],
                    'cursos'            => $this->formData['cursos']
                ];

                $registraTerminal = Database::insert([
                    'table'     => "sg_terminales",
                    'values'    => [
                        'id_sg_empresa'         => ModelGeneral::getIdEmpresaByUser($this->formData['uid']),
                        'id_sg_sede'            => (int) $this->formData['sede'],
                        'id_sg_estado'          => 1,
                        'id_sg_terminal_usuario'=> $getLastInsert,
                        'id_sg_tipo_registro'   => $this->formData['tipo'],
                        'id_sg_tipo_control'    => $this->modelGeneral->getTipoControlByUser($this->formData['uid']),
                        'formulario'            => json_encode($formulario),
                        'fecha_registro'        => Database::dateTime(),
                        'creado_por'            => $this->formData['uid']
                    ],
                    'autoinc'   => true
                ])->affectedRow();

                if($registraUsuarioTerminal && $registraTerminal)
                    return ['status' => true, 'message' => 'Usuario terminal registrado'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al crear el usuario de la terminal'];
            }
        } catch(Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function ReadById()
    {
        $resultSet = Database::storeProcedure("CALL obtenerDatosTerminal('". $this->formData['uid'] ."')")->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron Sedes'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public function ReadByEdit()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_terminal_usuarios stu JOIN sg_terminales st ON stu.`id_sg_terminal_usuario` = st.`id_sg_terminal_usuario`",
            'arguments' => "stu.id_sg_terminal_usuario = '". $this->formData['id'] ."'"
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron registros'];
        
        return [
            'status'    => true,
            'rows'      => $resultSet
        ];
    }

    public static function ReadTiposControl()
    {
        $resultSet = Database::query([
            'fields'    => "*",
            'table'     => "sg_tipo_control"            
        ])->records()->resultToArray();

        if(isset($resultSet[0]['empty']) && $resultSet[0]['empty'] == true)
            return ['status' => false, 'message' => 'No se encontraron datos'];
        
        $elements = [];

        foreach($resultSet as $i => $item)
        {
            $data = [
                'id'    => $item['id_sg_tipo_control'],
                'prop'  => $item['tipo_control']
            ];

            array_push($elements, $data);
        }

        return [
            'status'    => true,
            'combo'     => $elements
        ];
    }

    public function DeleteTerminal()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $deleteTerminal = Database::delete([
                    'table'     => "sg_terminal_usuarios",
                    'arguments' => "id_sg_terminal_usuario = '". $this->formData['id'] ."'"
                ])->deleteRow();

                if($deleteTerminal)
                    return ['status' => true, 'message' => 'Terminal Eliminada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al eliminar la Terminal'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function UpdateTerminal()
    {
        try {
            if(Validate::notEmptyFields($this->formData))
                return ['status' => false, 'message' => 'Los campos son obligatorios'];
            else
            {
                $updateTerminalUsuario = Database::update([
                    'table'     => "sg_terminal_usuarios",
                    'fields'    => [                        
                        'id_sg_estado'      => ($this->formData['status']) ? 1 : 2,
                        'usuario'           => $this->formData['nombreUsuario'],
                        'password'          => md5($this->formData['confirm']),
                        'recovery'          => $this->formData['confirm'],
                        'fecha_registro'    => Database::dateTime(),
                        'creado_por'        => $this->formData['uid']
                    ],
                    'arguments' => "id_sg_terminal_usuario = '". $this->formData['idTerminal'] ."'"
                ])->updateRow();

                $formulario = [
                    'c1'    => [
                        'type'          => 'text',
                        'required'      => false,
                        'placeholder'   => '',
                        'show'          => true
                    ],                    
                    'photo'             => $this->formData['photo'],
                    'eps'               => $this->formData['eps'],
                    'arl'               => $this->formData['arl'],
                    'cursos'            => $this->formData['cursos']
                ];

                $updateTerminal = Database::update([
                    'table'     => "sg_terminales",
                    'fields'    => [                        
                        'id_sg_sede'            => (int) $this->formData['sede'],
                        'id_sg_estado'          => ($this->formData['status']) ? 1 : 2,
                        'id_sg_terminal_usuario'=> $this->formData['idTerminal'],
                        'id_sg_tipo_registro'   => $this->formData['tipo'],
                        'id_sg_tipo_control'    => $this->modelGeneral->getTipoControlByUser($this->formData['uid']),
                        'formulario'            => json_encode($formulario)                                            
                    ],
                    'arguments' => "id_sg_terminal_usuario = '". $this->formData['idTerminal'] ."'"
                ])->updateRow();

                if($updateTerminalUsuario && $updateTerminal)
                    return ['status' => true, 'message' => 'Terminal Actualizada'];
                else
                    return ['status' => false, 'message' => 'Ha ocurrido un error al actualizar la Terminal'];
            }
        } catch (\Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}