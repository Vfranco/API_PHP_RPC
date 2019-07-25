<?php
/**
 * ================================
 *    Autoload System
 * ================================
 *
 */
spl_autoload_register(function ($nombre) {
    $file = dirname(APP_PATH) . "/" . str_replace("\\", "/", $nombre) . ".php";

    if (file_exists($file))
        require_once $file;
});