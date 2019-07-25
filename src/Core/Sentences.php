<?php

namespace Core;

use Database\Database;

class Sentences
{
    /**
     * $query
     *
     * @var [type]
     */
    private static $query;
    /**
     * insert
     *
     * @var [type]
     */
    private static $insert;
    /**
     * update
     *
     * @var [type]
     */
    private static $update;
    /**
     * delete
     *
     * @var [type]
     */
    private static $delete;
    /**
     * args
     *
     * @var [type]
     */
    private static $args;
    /**
     * table
     *
     * @var [type]
     */
    private static $table;
    /**
     * fields
     *
     * @var [type]
     */
    private static $fields;
    /**
     * values
     *
     * @var [type]
     */
    private static $values;
    /**
     * arguments
     *
     * @var [type]
     */
    private static $arguments;
    /**
     * debug
     *
     * @var [type]
     */
    private static $debug;
    /**
     * binding
     *
     * @var [type]
     */
    private static $binding;
    /**
     * autoinc
     *
     * @var [type]
     */
    private static $autoinc;

    /**
     * Construye la sentencia SELECT
     *
     * @param [array] $args
     * @return Context
     */
    public static function buildQuerySentence($args)
    {
        self::setArgument($args);
        return new static;
    }

    /**
     * Establece los argumentos
     *
     * @param [array] $args
     * @return void
     */
    private static function setArgument($args)
    {
        try{
            self::hasNoData($args);
        } catch (\Exception $e){
            print $e->getMessage();
        }        
    }

    /**
     * Verifica si los argumentos estan vacios
     *
     * @param [array] $args
     * @return boolean
     */
    private static function hasNoData($args)
    {
        if(empty($args) || count($args) <= 0)
            throw "Error: No argument set";
        else
            return self::hasData($args);
    }

    /**
     * Asigna los valores
     *
     * @param [array] $args
     * @return void
     */
    private static function hasData($args)
    {
        self::$table        = $args['table'];
        self::$fields       = (!isset($args['fields']) || empty($args['fields'])) ? '' : $args['fields'];
        self::$arguments    = (!isset($args['arguments']) || empty($args['arguments'])) ? '' : $args['arguments'];
        self::$autoinc      = (!isset($args['autoinc']) || empty($args['autoinc'])) ? '' : $args['autoinc'];
        self::$values       = (!isset($args['values']) || empty($args['values'])) ? '' : $args['values'];
        self::$debug        = (!isset($args['debug']) || empty($args['debug'])) ? '' : $args['debug'];
        self::$binding      = (!isset($args['binding']) || empty($args['binding'])) ? '' : $args['binding'];
    }

    /**
     * Crea la sentencia SELECT
     *
     * @return string
     */
    public static function makeQuery()
    {
        if(!empty(self::$arguments))
            self::$query = "SELECT " . self::$fields . " FROM " . self::$table . " WHERE " . self::$arguments;
        else
            self::$query = "SELECT " . self::$fields . " FROM " . self::$table;

        if(self::$debug)
            exit(self::$query);

        return self::$query;
    }

    /**
     * Crea la sentencia INSERT
     *
     * @param [type] $args
     * @return Context
     */
    public static function buildInsertSentence($args)
    {
        self::setArgument($args);
        return new static;
    }

    /**
     * stringBuilder INSERT
     *
     * @return string
     */
    public static function makeInsert()
    {
        self::$insert = "INSERT INTO " . self::$table;
        self::$insert .= " ( " . self::setFieldsFromTable() . " ) ";
        self::$insert .= "VALUES ( " . self::isAutoInc() . self::setFieldsFromValues() . " )";

        if(self::$debug)
            exit(self::$insert);

        return self::$insert;
    }

    /**
     * Define si es autoincrementable
     *
     * @return string
     */
    private static function isAutoInc()
    {
        if(self::$autoinc)
            return "'0', ";
    }

    /**
     * Establece los campos de la tabla
     *
     * @return string
     */
    private static function setFieldsFromTable()
    {
        $resource = DataBase::executeQuery("SHOW COLUMNS FROM " . self::$table);
        
        if(!mysqli_num_rows($resource) > 0)
            return false;

        $columns = array();

        while($rows = mysqli_fetch_assoc($resource))
            $columns[] = $rows['Field'];

        return implode(", ", $columns);
    }

    /**
     * Establece los valores de la tabla
     *
     * @return string
     */
    private static function setFieldsFromValues()
    {
        $values = array();

        if(empty(self::$values))
            return false;

        if(self::$binding)
        {
            if(is_array(self::$values))
                for($i = 0; $i < count(self::$values); $i++)
                    array_push($values, "'" . Database::escapeSql(self::$values[$i]['value']) . "'");
        }
        else if(is_array(self::$values))
            foreach(self::$values as $key => $value)
                array_push($values, "'" . Database::escapeSql($value) . "'");

        return implode(", ", $values);
    }

    /**
     * Construye sentence DELETE
     *
     * @param [array] $args
     * @return Context
     */
    public static function buildDeleteSentence($args)
    {
        self::setArgument($args);
        return new static;
    }

    /**
     * stringBuilder DELETE
     *
     * @return string
     */
    public static function makeDelete()
    {
        self::$delete = "DELETE FROM " . self::$table . " WHERE " . self::$arguments;

        if(self::$debug)
            exit(self::$delete);

        return self::$delete;
    }

    /**
     * Crea la sentencia UPDATE
     *
     * @param [array] $args
     * @return Context
     */
    public static function buildUpdateSentence($args)
    {
        self::setArgument($args);
        return new static;
    }

    /**
     * stringBuilder UPDATE
     *
     * @return string
     */
    public static function makeUpdate()
    {
        self::$update = "UPDATE " . self::$table;
        self::$update .= " SET " . self::setFieldsToEdit() . " WHERE " . self::$arguments;

        if(self::$debug)
            exit(self::$update);

        return self::$update;
    }

    /**
     * Establece Campos para Editar
     *
     * @return string
     */
    private static function setFieldsToEdit()
    {
        $setFields = array();

        if(!is_array(self::$fields) || count(self::$fields) <= 0 || empty(self::$fields))
            return false;
        else
            foreach(self::$fields as $field => $value)
                array_push($setFields, $field . " = '" . $value . "'");

        return implode(",", $setFields);
    }
}