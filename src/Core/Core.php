<?php

namespace Core;

use Core\Views;

class Core
{
    static function ApplicationStart()
    {
        return new Server(
        [
            'method'    => REQUEST_METHOD,
            'route'     => REQUEST_URI,
            'landing'   => function()
            {
                return Views::add('app.index');
            }
        ]);
    }
}

Core::ApplicationStart();