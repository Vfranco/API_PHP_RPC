<?php

namespace Core;

use AppLib\Http\Route;

class Server
{
    /**
     * ROOT
     */
    private $path_root  = '/';

    /**
     * request method
     */

    private $method;

    /**
     *  set the controller to execute
     */   
    private $controller;

    /**
     *  set the action from the controller to execute
     */
    private $action;

    /**
     * set the id in case is defined | optional
     */
    private $id;

    /**
     * Data Collection from Request
     */
    private $collection = [];

    /**
     * Landing set the landing page or the Index
     */
    private $landing = '';

    /**
     * __construct Init Verbs and Method Request
     *
     * @param  mixed $uri
     *
     * @return void
     */
    public function __construct($uri)
    {
        try{
            $this->method   = $uri['method'];
            $this->landing  = $uri['landing'];

            $this->checkURI($uri);
        } catch (\Exception $e){
            print $e->getMessage();
        }        
    }

    /**
     * checkURI : check the elements on collection
     *
     * @param  mixed $uri
     *
     * @return void
     * @throws \Exception
     */
    private function checkURI($uri)
    {
        if(empty($uri) || count($uri) <= 0)
            throw new \Exception("Error : Arguments Not Defined");
        else
            self::setValues($uri);            
    }

    /**
     * setValues : set the values on collection
     *
     * @param  mixed $uri
     *
     * @return void
     */
    private function setValues($uri)
    {
        if(preg_match_all('/\/(\w+)/', $uri['route'], $elements))
            $this->collection = $elements;

        if(count($this->collection) <= 0 || REQUEST_URI === $this->path_root || empty($this->collection))
            $this->landing($this->landing);
        else
            self::setRoute();        
    }

    /**
     * set the elementos to route the request
     * @return bool
     * @throws \Exception
     */
    private function setRoute()
    {
        if(empty($this->collection[1][1]))
            return false;

        $this->controller   = $this->collection[1][0];
        $this->action       = $this->collection[1][1];

        if(empty($this->controller) && empty($this->action))
            return false;
        else
        {
            if(isset($this->collection[1][2]))
            $this->id = $this->collection[1][2];

            try{
                $this->processRequest();
            } catch(\Excepetion $e){
                print $e->getMessage();
            }
        }
    }

    /**
     * Landing Page
     *
     * @param [callabel] $callback
     * @return void
     */
    private function landing($callback)
    {
        if(!is_string($callback))
            if(is_callable($callback))
                return call_user_func($callback);
            else
                Response::status(200)->json(['Start Page' => 'Landing']);
    }

    /**
     * processRequest : make the request about the verb request
     *
     * @return void
     */
    private function processRequest()
    {
        switch($this->method)
        {
            case 'GET':                
                $this->getRequest();                
            break;

            case 'POST':                
                $this->postRequest();
            break;

            case 'PUT':
                $this->putRequest();
            break;

            case 'DELETE':
                $this->deleteRequest();
            break;

            case 'OPTIONS':
                $this->optionRequest();
            break;

            default:
                throw new \Exception("Error : Verb Not Allowed");
            break;
        }
    }

    /**
     * getRequest : is the GET request process
     *
     * @return void
     */
    private function getRequest()
    {    
        if(!empty($this->id))
        {
            $uri = $this->collection[0][0] . $this->collection[0][1];
            Route::get($uri . "/{id}", ucwords($this->controller) . '@' . ucwords($this->action));
        }
        else
        {            
            $uri = $this->collection[0][0] . $this->collection[0][1];            
            Route::get($uri, ucwords($this->controller) . '@' . ucwords($this->action));
        }
    }

    /**
     * postRequest : is the POST request process
     *
     * @return void
     */
    private function postRequest()
    {    
        if(!empty($this->id))
        {
            $uri = $this->collection[0][0] . $this->collection[0][1];            
            Route::post($uri . "/{id}", ucwords($this->controller) . '@' . ucwords($this->action));
        }
        else
        {
            $uri = $this->collection[0][0] . $this->collection[0][1];
            Route::post($uri, ucwords($this->controller) . '@' . ucwords($this->action));
        }        
    }

    /**
     * putRequest : is the PUT request process
     *
     * @return void
     */
    private function putRequest()
    {    
        if(!empty($this->id))
        {
            $uri = $this->collection[0][0] . $this->collection[0][1];            
            Route::put($uri . "/{id}", ucwords($this->controller) . '@' . ucwords($this->action));
        }
        else
        {
            $uri = $this->collection[0][0] . $this->collection[0][1];
            Route::put($uri, ucwords($this->controller) . '@' . ucwords($this->action));
        }
    }

    /**
     * deleteRequest : is the DELETE request process
     *
     * @return void
     */
    private function deleteRequest()
    {    
        if(!empty($this->id))
        {
            $uri = $this->collection[0][0] . $this->collection[0][1];            
            Route::delete($uri . "/{id}", ucwords($this->controller) . '@' . ucwords($this->action));
        }
        else
        {
            $uri = $this->collection[0][0] . $this->collection[0][1];
            Route::delete($uri, ucwords($this->controller) . '@' . ucwords($this->action));
        }        
    }

    /**
     * deleteRequest : is the DELETE request process
     *
     * @return void
     */
    private function optionRequest()
    {    
        if(!empty($this->id))
        {
            $uri = $this->collection[0][0] . $this->collection[0][1];
        }
        else
        {
            $uri = $this->collection[0][0] . $this->collection[0][1];
        }        
    }
}