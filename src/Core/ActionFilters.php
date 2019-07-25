<?php

namespace Core;

use AppLib\Http\Response;
use AppLib\Http\Redirect;
use JWT\src\JWT;
use Models\OTAuthentication;

class ActionFilters
{
    /**
     * Get : Denied Access from GET request
     *
     * @return void
     */
    static function Get()
    {
        if (GET)
            exit(Response::status(403)->json(['status' => 'Access Denied']));
    }

    /**
     * Post : Denied Access from POST request
     *
     * @return void
     */
    static function Post()
    {
        if (POST)
            exit(Response::status(403)->json(['status' => 'Access Deneid']));
    }

    /**
     * SessionActive : Check the Active Session on API
     *
     * @return void
     */
    static function SessionActive()
    {
        if (count($_SESSION) <= 0)
            Redirect::RedirectTo('');
    }

    static function dontApply($str)
    {
        if(empty($str))
            return true;

        return false;
    }
    /**
     * checkTokenTransaction : check the token on every transaction on request
     * @param $randomkey
     * @return mixed
     */
    static function checkTokenTransaction($randomkey)
    {
        try {
            return Validate::isNotEmpty($randomkey);
        } catch (\Exception $e) {
            Response::status(200)->json($e->getMessage());
        }
    }

    /**
     * compareRandomKey : Compare the randomKey from request and the database
     *
     * @param [type] $request
     * @return array
     */
    static function compareRandomKey($request)
    {
        $response = ['status' => false, 'message' => 'Invalid Random Key'];
        if (!isset($request['randomkey'])) {
            return $response;
        }
        $cmsMobile = \CmsMobileQuery::create()
            ->filterByRandomKey($request['randomkey'])
            ->filterByIdCmsEstadosMobileFk(_ESTADO_ACTIVO)
            ->findOne();

        if ($cmsMobile instanceof \CmsMobile)
            $randomKey = $cmsMobile->getRandomKey();
        else
            return $response;

        return self::decodeTokenRandom($randomKey, $request['token']);
    }

    static function decodeTokenRandom($randomKey, $token)
    {
        $cmsSecret = \CmsMobileQuery::create()
            ->filterByRandomKey($randomKey)
            ->filterByIdCmsEstadosMobileFk(_ESTADO_ACTIVO)
            ->findOne();

        if ($cmsSecret instanceof \CmsMobile)
            $secret = $cmsSecret->getSecretKey();
        else
            return ['status' => false, 'message' => 'Secret Key not found on Database'];

        $dataToSignRandomKey = [
            'randomkey' => $randomKey
        ];

        $tokenRandomKey = JWT::encode($dataToSignRandomKey, $secret);

        try {
            $decodeToken = JWT::decode($tokenRandomKey, $secret, array('HS256'));
            $decodeTokenRequest = JWT::decode($token, $secret, array('HS256'));

            $resultDecode = (array)$decodeTokenRequest;


            if ($resultDecode['token'] === $randomKey) {
                return [
                    'status' => true,
                    'message' => 'Success Transaction',
                    'randomkey' => OTAuthentication::saveRandomKey(Validate::randomKey(12), $secret)
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Random Key dont Match'
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    static function executeQuery($query)
    {
        try {
            $con = \Propel::getConnection();
            $sql = $query;

            $stmt = $con->prepare($sql);

            if (!$stmt->execute())
                exit();

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $result;

        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }
}