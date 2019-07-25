<?php

namespace AppLib\Interfaces;

interface IRouting
{
    public static function get($uri, $callback);

    public static function post($uri, $callback);
}