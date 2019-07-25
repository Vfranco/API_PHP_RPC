<?php

namespace AppLib\Http;

class Response
{
    /**
     * status : Define the status code
     *
     * @param  mixed $status
     *
     * @return Context
     */
    static function status($status)
    {        
        header("HTTP/1.1 $status " . self::label($status));
        return new static;
    }

    /**
     * label : Define the name of status code
     *
     * @param  mixed $status
     *
     * @return void
     */
    private static function label($status)
    {
        if(empty($status))
            throw new \Exception('Error : label not defined');

        switch($status)
        {
            case 200:
                'OK';
            break;

            case 201:
                'Created';
            break;           

            case 400:
                'Bad Request';
            break;

            case 403:
                'Forbidden';
            break;

            case 404:
                'Not Found';
            break;

            case 500:
                'Internal Server Error';
            break;
        }
    }

    /**
     * json : return the JSON Response
     *
     * @param  mixed $arg
     *
     * @return void
     */
    static function json($arg = [])
    {
        try{
            echo json_encode(self::checkArray($arg), JSON_PRETTY_PRINT);
        } catch(\Exception $e){
            echo json_encode(['error' => $e->getMessage()]);
        }        
    }

    /**
     * @param $data
     * @return mixed
     */
    static function toArray($data)
    {
        return $data;
    }

    /**
     *  json : return JSON decode
     */
    static function decode($raw)
    {
        return json_decode($raw, true);
    }

    /**
     * checkArray : check the elements on array
     *
     * @param  mixed $arg
     *
     * @return void
     */
    private static function checkArray($arg)
    {        
        if(!isset($arg) || count($arg) <= 0)
            throw new \Exception('Error : Array not defined');

        return $arg;
    }
}