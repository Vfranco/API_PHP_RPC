<?php

namespace Core;

use AppLib\Http\Response;

class Validate
{
    private static $responseEmpty = ['status' => false, 'message' => 'Empty Request'];
    private static $noProperties = ['status' => false, 'message' => 'No Properties are defined'];
    private static $noRandomKey = ['status' => false, 'message' => 'No RandomKey are defined'];

    static function Request($request)
    {
        if (count($request) <= 0 || empty($request))
            throw new \Exception(Response::status(200)->json(self::$responseEmpty));
        else
            foreach ($request as $key => $value) {
                if (empty($value) || !isset($key))
                    throw new \Exception(Response::status(200)->json(self::$noProperties));
            }


        return $request;
    }

    static function notEmptyFields($data)
    {
        if(!isset($data) || empty($data))
            throw new \Exception(_ERROR_BODY_BAD_FORMED);
        
        $status = false;
        
        foreach($data as $key => $value)
        {
            if(empty($value))
            {
                $status = true;
                break;
            }            
        }
        
        return $status;
    }

    static function isNotEmpty($str)
    {
        if (!isset($str) || empty($str) || count($str) <= 0)
            throw new \Exception(Response::status(200)->json(self::$noRandomKey));

        return $str;
    }

    static function isNotNull($obj)
    {
        if (is_null($obj))
            throw new \Exception("Error : Object " . $obj . " is NULL");
    }

    static function randomKey($length = 6)
    {
        $str = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max = count($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }

        return $str;
    }

    static function randomNumber($length = 11)
    {
        $str = "";
        $characters = array_merge(range('0', '9'));
        $max = count($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }

        return $str;
    }

    /**
     * Validamos que existan los campos requeridos por un metodo
     * @param array $arrayFields
     * @param array $arrayValidate
     * @return bool
     */
    static function validaFields(array $arrayFields, array $arrayValidate)
    {
        foreach ($arrayValidate as $keyValidate) {
            if (!array_key_exists($keyValidate, $arrayFields)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Validamos los campos requeridos
     * @param array $arrayFields
     * @param array $arrayValidate
     * @return bool
     */
    static function validaFieldsRequire(array $arrayFields, array $arrayValidate)
    {
        foreach ($arrayValidate as $keyValidate) {
            if (!array_key_exists($keyValidate, $arrayFields)) {
                return false;
            } else if (empty($keyValidate)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Verificamos si una cadena es un string
     * @param $md5
     * @return false|int
     */
    static function isValidMd5($md5)
    {
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }
}