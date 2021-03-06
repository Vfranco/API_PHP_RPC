<?php

/**
 * =====================================
 *  Globals Config
 * =====================================
 */

/**
 * App Title, Version, BaseUrl
 */
define('APP_TITLE',     'Api Sigga');
define('CMS_VERSION',   '1.0');

/**
 * BASE_URL - Local
 */
define('BASE_URL', 'http://localapi.sigga.com/');

/**
 * BASE_URL - Server
 */
//define('BASE_URL', 'http://site.nomasplanillas.com/');

/**
 * Action Verbs
 */
define('POST',    $_SERVER['REQUEST_METHOD'] === 'POST');
define('GET',     $_SERVER['REQUEST_METHOD'] === 'GET');
define('PUT',     $_SERVER['REQUEST_METHOD'] === 'PUT');
define('DELETE',  $_SERVER['REQUEST_METHOD'] === 'DELETE');
define('OPTIONS', $_SERVER['REQUEST_METHOD'] === 'OPTIONS');
define('PATCH',   $_SERVER['REQUEST_METHOD'] === 'PATCH');

/**
 * Request Method
 */

define('REQUEST_METHOD',  $_SERVER['REQUEST_METHOD']);
define('REQUEST_URI',     $_SERVER['REQUEST_URI']);
define('CONTROLLER_PATH', 'Controllers');
define('_REMOTE_ADDR_GENERAL', $_SERVER['REMOTE_ADDR']);

/**
 * Menu por Defecto
 */
define('MENU_DEFAULT', [
    'Mi Personal'       => [
        'href'      => '#!/empresas',
        'icon'      => 'badge',
        'color'     => 'primary',
        'estado'    => 1
    ],
    'Mis Proveedores'   => [
        'href'      => '#!/proveedores',
        'icon'      => 'building',
        'color'     => 'yellow',
        'estado'    => 2
    ],
    'Mis Visitantes'    => [
        'href'      => '#!/visitantes',
        'icon'      => 'circle-08',
        'color'     => 'red',
        'estado'    => 2
    ]]
);

/*
* Security Controls 
*/
date_default_timezone_set('America/Bogota');

/**
 * Textos Globales
 */
define('_MSGBOX_GENERAL_ERROR', 'Lo sentimos, ocurrió un error en el sistema. Por favor, intente nuevamente.');
define('_MSGBOX_NOT_REGISTER_MOBILE', 'El dispositivo no se encuentra registrado en el sistema.');
define('_MSGBOX_ERROR_DATE_MOBILE', 'Por favor verifique la fecha del dispositivo.');
define('_MSGBOX_ERROR_ZONA', 'El dispositivo no tiene asignado una zona valida.');
define('_MSGBOX_ERROR_AUTHENTICATION', 'Usuario y/o Contraseña Invalidos');
define('_ERROR_AUTH_ERROR', 'Combinación usuario/contraseña no es correcta.');
define('_ERROR_AUTH_TOKEN', 'El token del login no es valido.');
define('_ERROR_AUTH_SESSION', 'Ya existes una sesión desde otro dispositivo.');
define('_ERROR_BODY_BAD_FORMED', 'Cuerpo mal formado, por favor verifica que el body se encuentre bien estructurado y que los datos no esten vacios');

/**
 * 
 */
define('_ERROR_USER_BLOCK', 'La empresa se encuentra bloqueada.');
define('_ERROR_USER_NOT_EXIST', 'El usuario no se encuentra activo en el sistema.');
define('_ERROR_CLIENTE_SIN_TRAMITE', 'El número de documento no tiene tramite activo en el sistema.');


/**
 * ID ESTADOS
 */

define('_ID_ESTADO_ACTIVO',1);
define('_ID_ESTADO_INACTIVO',2);
define('_ID_ESTADO_ELIMINADO',3);
define('_ID_ESTADO_PROCESADO',5);
define('_ID_ESTADO_IMPRESA',6);
define('_OTRA_ACTIVIDAD', 'Otra');
