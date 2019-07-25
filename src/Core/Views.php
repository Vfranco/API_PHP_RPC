<?php

namespace Core;

class Views
{
    /**
     * Path of the application
     *
     * @var [type]
     */
    private static $path = APP_PATH;

    /**
     * add : Add a View Resource the data if exist data from model
     *
     * @param [string] $view
     * @param array $data
     * @param [instance] $formCreator
     * @return void
     */
    static function add($view, $data = [], $formCreator = null)
    {
        try {
            $path = dirname(self::$path) . "/Views/" . str_replace('.', '/', $view) . ".php";
            self::checkResource($path, $data, $formCreator);
        } catch (Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * addLayout : set the Header or Footer on View
     *
     * @param [string] $resource
     * @return void
     */
    static function addLayout($resource)
    {
        try {
            $path = dirname(self::$path) . "/Layouts/" . str_replace('.', '/', $resource) . ".php";
            self::checkResource($path, [], null);
        } catch (Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * @param $view
     * @param $data
     * @param $formCreator
     * @throws \Exception
     */
    private static function checkResource($view, $data, $formCreator)
    {
        if (!file_exists($view))
            throw new \Exception('Error : Resource not found');
        else
            self::setResource($view, $data, $formCreator);
    }

    /**
     * @param $view
     * @param $data
     * @param $formCreator
     */
    private static function setResource($view, $data, $formCreator)
    {
        include $view;
    }
}