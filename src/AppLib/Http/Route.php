<?php

namespace AppLib\Http;

use AppLib\Interfaces\IRouting;

class Route implements IRouting
{
    /**
     * variable : uri
     *
     * @var [type]
     */
    private $uri;

    /**
     * variable : callback
     *
     * @var [type]
     */
    private $callback;

    /**
     * varaiable requestUri
     *
     * @var [type]
     */
    private $requestUri;

    /**
     * variable pathToController
     *
     * @var string
     */
    private $pathToController = CONTROLLER_PATH;

    /**
     * variable controller
     *
     * @var [type]
     */
    private $controller;

    /**
     * variable params
     *
     * @var array
     */
    private $params = array();

    /**
     * Method: Constructor
     * Inicializa la gestion de la clase
     *
     * @param [type] $uri
     * @param [type] $callback
     * @return void
     */
    public function __construct($uri, $callback)
    {
        $this->uri = $uri;
        $this->callback = $callback;
        $this->requestUri = $_SERVER['REQUEST_URI'];

        return $this->startRouting();
    }

    /**
     * Method: get
     * Se define como el controlador para peticiones get a la API o Recurso
     *
     * @param [type] $uri
     * @param [type] $callback
     * @return void
     */
    public static function get($uri, $callback)
    {
        if (GET)
            return $obj = new Route($uri, $callback);
    }

    /**
     * Method: post
     * Se define como el controlador para peticiones post a la API o Recurso
     *
     * @param [type] $uri
     * @param [type] $callback
     * @return void
     */
    public static function post($uri, $callback)
    {
        if (POST)
            return $obj = new Route($uri, $callback);
    }

    /**
     * Method: put
     * Se define como el controlador para peticiones post a la API o Recurso
     *
     * @param [type] $uri
     * @param [type] $callback
     * @return void
     */
    public static function put($uri, $callback)
    {
        if (PUT)
            return $obj = new Route($uri, $callback);
    }

    /**
     * Method: delete
     * Se define como el controlador para peticiones post a la API o Recurso
     *
     * @param [type] $uri
     * @param [type] $callback
     * @return void
     */
    public static function delete($uri, $callback)
    {
        if (DELETE)
            return $obj = new Route($uri, $callback);
    }

    /**
     * Method: options
     * Se define como el controlador para peticiones post a la API o Recurso
     *
     * @param [type] $uri
     * @param [type] $callback
     * @return void
     */
    public static function options($uri, $callback)
    {
        if (OPTIONS)
            return $obj = new Route($uri, $callback);
    }

    /**
     * Method: startRouting
     * Inicia el procesamiento del route system
     *
     * @return boolean
     */
    private function startRouting()
    {
        $this->processRequest();
    }

    /**
     * Method: processRequest
     * Procesa la solicitud por GET
     *
     * @return void
     */
    private function processRequest()
    {
        if ($this->checkCallback())
            $this->executeCallBack();
        else
            $this->executeClass();
    }

    /**
     * Method: executeCallback
     * Ejecuta el callback asignado
     *
     * @return void
     */
    private function executeCallBack()
    {
        if ($this->compareUri()) {
            if (!$this->hasParams())
                if (is_callable($this->callback))
                    return call_user_func($this->callback);
                else
                    $this->executeCallbackWithParams();
        }
    }

    /**
     * Method : checkCallback
     * Verifica el parametro de entrada del callback
     *
     * @return void
     */
    private function checkCallback()
    {
        return (!is_string($this->callback)) ? true : false;
    }

    /**
     * Method: executeClass
     * Ejecuta la clase asociada como segundo argumento de los metodos get y post
     *
     * @return void
     */
    private function executeClass()
    {
        try {
            return $this->checkClass();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Method : execute CallBackWithParams
     * Ejecuta el callback si existen parametros
     *
     * @return void
     */
    private function executeCallbackWithParams()
    {
        return call_user_func_array($this->callback, $this->getParams());
    }

    /**
     * Method: checkClass
     * Determina la correcta definicion del recurso callback
     *
     * @return void
     */
    private function checkClass()
    {
        if (empty($this->callback) || count($this->separateString()) <= 1)
            throw new \Exception('Error: Bad Definition of Class');
        else
            try {
                $this->classExist();
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
    }

    /**
     * Determina la existencia del recurso
     * @throws \Exception
     */
    private function classExist()
    {
        $this->controller = $this->separateString();
        $className = $this->pathToController . "\\" . $this->controller[0];
        $methodName = $this->controller[1];

        if (!class_exists($className, true))
            throw new \Exception('Error: Class Dont Exist');
        else 
        {
            if (!method_exists($className, $methodName))
                throw new \Exception('Error: Method Dont Exist on Class ' . $this->controller[0]);
            else
                $this->executeController();
        }
    }


    /**
     * Control del recurso entregado para su posterior ejecucion
     * @return array
     */
    private function separateString(): array
    {
        return explode('@', $this->callback);
    }

    /**
     * Method : executeController
     * Ejecuta el controlador de acuerdo a la asignacion en los metodos get y post
     *
     * @return void
     */
    private function executeController()
    {        
        if ($this->compareUri())
        {
            if (!$this->hasParams())
                return call_user_func($this->pathToController . "\\" . $this->controller[0] . '::' . $this->controller[1]);
            else
                $this->executeControllerWithParams();
        }
    }

    /**
     * Method: compareUri
     * Compara los request de los metodos solicitados
     *
     * @return void
     */
    private function compareUri()
    {
        $status = false;

        if ($this->checkPath())
            return $status = true;

        if (preg_match('/\/(\w+)\/\w+/', $this->uri, $uri) and preg_match('/\/(\w+)\/\w+/', $this->requestUri, $req)) {
            if ($uri[0] === $req[0])
                $status = true;
        }

        return $status;
    }

    /**
     * Method : checkPath
     * verifica el PATH de la API
     *
     * @return void
     */
    private function checkPath()
    {
        $status = false;

        if ($this->uri === '/' and $this->requestUri === '/')
            $status = true;
        else
            $status = false;

        return $status;
    }

    /**
     * Method: executeControllerWithParams
     * Ejecuta el recurso asignado con los parametros pasados al metodo
     *
     * @return void
     */
    private function executeControllerWithParams()
    {
        if (count($this->getParams()) <= 0)
            throw new \Exception('Error: Args not defined');
        else
            return call_user_func_array($this->pathToController . "\\" . $this->controller[0] . '::' . $this->controller[1], $this->getParams());
    }

    /**
     * Method: hasParams
     * Determina si el recurso tiene parametros
     *
     * @return boolean
     */
    private function hasParams()
    {
        if (preg_match_all('/\/{(\w+)\}(?:\/)?$/', $this->uri, $output))
            return true;
        else
            return false;
    }

    /**
     * Method: getParams
     * Obtiene el resultado de los argumentos de la URL
     *
     * @return array
     */
    private function getParams()
    {
        if (preg_match_all('/(\w+..)\/?(?:$)/', $this->requestUri, $output))
            $this->params[] = $output[0];

        return $this->params[0];
    }
}