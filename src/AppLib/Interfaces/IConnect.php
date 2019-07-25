<?php

namespace AppLib\Interfaces;

interface IConnect
{
    static function open();
    
    static function close();
}