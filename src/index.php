<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

/**
 * Enviroment
 */

include 'Config/Enviroment.php';

/**
 * Globals Config
 */

include 'Config/Globals.php';

/**
 * vendor autoload
 */

require '../vendor/autoload.php';

/**
 * Autoload Class API
 */

include 'autoload.php';
 
/**
 * API Core
 */

include 'Core/Core.php';