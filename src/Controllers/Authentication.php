<?php

namespace Controllers;

use AppLib\Http\{ Request, Response };
use Core\ActionFilters;
use Models\Authentication\ModelAuthentication;

class Authentication
{
    static function login()
    {
        ActionFilters::Get();

        $auth = new ModelAuthentication(Request::phpInput());
        Response::status(200)->json($auth->doLogin());
    }

    static function logout()
    {
        ActionFilters::Get();
        $auth = new ModelAuthentication(Request::phpInput());
        Response::status(200)->json($auth->doLogout());
    }

    static function cmslogin()
    {
        ActionFilters::Get();
        $auth = new ModelAuthentication(Request::phpInput());
        Response::status(200)->json($auth->cmsLogin());
    }
}