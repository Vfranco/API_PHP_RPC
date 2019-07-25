<?php

namespace Database;

class Connect
{
    private static $server      = SERVER;
    private static $user        = USER;
    private static $pass        = PSWD;
    private static $database    = DATABASE;

    static function open()
    {
        $con = mysqli_connect(self::$server, self::$user, self::$pass, self::$database);
        return $con;
    }

    static function close()
    {
        return mysqli_close(self::open());
    }
}