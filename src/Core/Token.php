<?php

namespace Core;

use AppLib\JWT\src\JWT;

class Token
{
    static function create($secretKey, $randomKey)
    {
        try{
            return JWT::encode($randomKey, $secretKey);
        } catch(\Exception $e){
            print $e->getMessage();
        }        
    }

    static function isEmpty($token)
    {
        if(!isset($token) || empty($token))
            return 'N/A';

        return $token;
    }
}