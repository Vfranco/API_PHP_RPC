<?php

namespace Database;

use Core\Statement;
use Database\Connect;

class Database extends Statement
{
    /**
     * querySentence
     *
     * @var [resultSet]
     */
    private static $querySentence;
    private static $insertSentence;
    private static $updateSentence;
    private static $deleteSentence;
    /**
     * records
     *
     * @var [array] || [json]
     */
    private static $records;
    public static $lastid;
        
    /**
     * Crea la sentencia SELECT
     *
     * @param [array] $args
     * @return Context
     */
    public static function query($args)
    {
        self::$querySentence = self::executeQuery(self::buildQuerySentence($args)->makeQuery());
        return new static;
    }

    /**
     * Crea la sentencia INSERT
     *
     * @param [array] $args
     * @return Context
     */
    public static function insert($args)
    {
        self::$insertSentence = self::executeQuery(self::buildInsertSentence($args)->makeInsert());
        self::$lastid = mysqli_insert_id(Connect::open());

        return new static;
    }

    /**
     * Crea la sentencia UPDATE
     *
     * @param [array] $args
     * @return Context
     */
    public static function update($args)
    {
        self::$updateSentence = self::executeQuery(self::buildUpdateSentence($args)->makeUpdate());
        return new static;
    }

    /**
     * Crea la sentencia DELETE
     *
     * @param [array] $args
     * @return void
     */
    public static function delete($args)
    {
        self::$deleteSentence = self::executeQuery(self::buildDeleteSentence($args)->makeDelete());
        return new static;
    }

    /**
     * Ejecuta SP
     *
     * @param [string] $query
     * @return Context
     */
    public static function storeProcedure($query)
    {
        self::$querySentence = self::executeQuery($query);
        return new static;
    }

    /**
     * Ejecuta las sentencias
     *
     * @param [string] $query
     * @return resultSet
     */
    public static function executeQuery($query)
    {
        return mysqli_query(Connect::open(), $query);
    }

    /**
     * Verifica las filas
     *
     * @return boolean
     */
    public static function rows()
    {
        return (self::$querySentence == false || mysqli_num_rows(self::$querySentence) <= 0) ? false : true;
    }

    /**
     * Obtiene un valor asociado a un resultSet
     *
     * @param [string] $arg
     * @return string
     */
    public static function assoc($arg)
    {
        if(empty($arg))
            return self::assocRecords();
        else
        {
            $resource = self::assocRecords();
            self::freeResult(self::$querySentence);

            return $resource[$arg];
        }
    }

    /**
     * Toma los registros de un resultSet
     *
     * @return Context
     */
    public static function records()
    {
        $output = [];

        if(self::rows() <= 0)
            $output[] = array('empty' => true);
        else
            while($rows = self::assocRecords())                            
                $output[] = $rows;

        self::$records = $output;
        
        self::freeResult(self::$querySentence);
        Connect::close();

        return new static;
    }

    /**
     * Asociacion de registros
     *
     * @return resultset
     */
    public static function assocRecords()
    {
        return mysqli_fetch_assoc(self::$querySentence);
    }

    /**
     * Devuelve un arreglo de acuerdo al resultset
     *
     * @return array
     */
    public static function resultToArray()
    {
        return self::$records;
    }

    /**
     * Devuelve un JSON de acuerdo al resultset
     *
     * @return JSON
     */
    public static function resultToJson()
    {
        return json_encode(self::$records);
    }

    /**
     * Determina si hubo cambios o filas afectadas
     *
     * @return boolean
     */
    public static function affectedRow()
    {
        return self::$insertSentence;
    }

    /**
     * Determina si hubo actualizaciones en la fila o filas afectadas
     *
     * @return void
     */
    public static function updateRow()
    {
        return self::$updateSentence;
    }

    /**
     * Determina si hubo eliminacion de registros
     * 
     */
    public static function deleteRow()
    {
        return self::$deleteSentence;
    }

    /**
     * Obtiene la fecha y hora del servidor
     *
     * @return void
     */
    public static function dateTime()
    {
        $resultSetDateTime = self::executeQuery('SELECT DATE_FORMAT(now(), "%Y-%m-%d %h:%i:%s") as fechaHoraServidor');

        $datetime = mysqli_fetch_assoc($resultSetDateTime);
        self::freeResult($resultSetDateTime);

        return $datetime['fechaHoraServidor'];
    }

    /**
     * Obtiene la Fecha del Servidor
     *
     * @return void
     */
    public static function date()
    {
        $resultSetDate = self::executeQuery('SELECT DATE_FORMAT(now(), "%Y-%m-%d") as `fechaServidor`');

        $date = mysqli_fetch_assoc($resultSetDate);
        self::freeResult($resultSetDate);

        return $date['fechaServidor'];
    }

    /**
     * Obtiene la Hora
     *
     * @return void
     */
    public static function time()
    {
        $resultSetTime = self::executeQuery('SELECT DATE_FORMAT(now(), "%h:%i:%s") as `horaServidor`');

        $time = mysqli_fetch_assoc($resultSetTime);
        self::freeResult($resultSetTime);

        return $time['horaServidor'];
    }

    /**
     * Limpia la cadena de los valores
     *
     * @param [string] $str
     * @return void
     */
    public static function escapeSql($str)
    {
        return mysqli_real_escape_string(Connect::open(), $str);
    }

    /**
     * Librea Recursos
     *
     * @param [resultset] $resulset
     * @return void
     */
    private static function freeResult($resulset)
    {
        return mysqli_free_result($resulset);
    }
}