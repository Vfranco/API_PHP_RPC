<?php

namespace AppLib\Http;

class Request
{
    static function input($arg)
    {
        if (!isset($_POST[$arg]))
            ;
        else
            return $_POST[$arg];
    }

    static function inputAll()
    {
        if (self::ispost())
            return $_POST;
    }

    static function phpInput()
    {
        $input = file_get_contents('php://input');

        if (self::isPost())
        {
            if ($_SERVER['CONTENT_TYPE'] === 'application/json;charset=UTF-8')
                parse_str($input, $_POST);            
        }

        return self::decode($input);
    }

    static function queryString($arg)
    {
        if (!isset($_GET[$arg]))
            return false;
        else if (self::isget())
            return $_GET[$arg];
    }

    static function params($arg)
    {
        return $_GET[$arg];
    }

    static function isPost()
    {
        return POST;
    }

    static function isGet()
    {
        return GET;
    }

    static function requestUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    static function decode($input)
    {
        return json_decode($input, true);
    }

    static function apiResource(array $postData, string $urlController)
    {
        $resultCurl = ['result' => 'Not data Received'];

        try {
            $ch = curl_init();

            $headers = [
                'x-api-key: XXXXXX',
                'Content-Type: application/json'
            ];

            curl_setopt($ch, CURLOPT_URL, $urlController);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_COOKIEFILE,'cookie.txt');
            curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');


            $result = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($statusCode == 200)
            {
                $result = json_decode($result, true);

                if (isset($result["status"]))
                    return $result;
            }

            return $resultCurl;

        } catch (\Exception $e) {
            return $e->getMessage();
        }        
    }
}