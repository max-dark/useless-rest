<?php
/**
 * @copyright Copyright (C) 2016. Max Dark maxim.dark@gmail.com
 * @license MIT; see LICENSE.txt
 */

namespace useless\rest;

/**
 * Class Router
 * @package useless\rest
 */
class Router
{
    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var \useless\abstraction\Storage
     */
    private $storage;

    /**
     * Router constructor.
     * @param \useless\abstraction\Storage $storage
     * @param array $routes
     */
    public function __construct($storage, $routes)
    {
        $this->routes = $routes;
        $this->storage = $storage;
    }

    /**
     * @param string $method
     * @param string $url
     * @return array
     */
    public function dispatch($method, $url)
    {
        $method = strtolower($method);
        $matched = false;
        foreach ($this->routes as $route => $action) {
            list($currentMethod, $currentUrl) = explode(':', $route, 2);
            if ($currentMethod !== $method) {
                continue;
            }
            if ($currentUrl === $url) {
                $matched = true;
                $className = $action['class'];
                $methodName = $action['action'];
                $paramList = $this->getParams($method, $action['params']);
                if (false !== $paramList) {
                    $controller = new $className($this->storage);
                    return call_user_func_array([$controller, $methodName], $paramList);
                }
            }
        }
        return [
            'status' => $matched ? 'Missing required parameters' : 'Requested method does not exist.'
        ];
    }

    /**
     * Extract requested params
     *
     * @param string $method HTTP method in lower case
     * @param string[] $names array of param names
     *
     * @return array|bool false if not all params found
     */
    private function getParams($method, $names)
    {
        $params = [];
        switch ($method) {
            case 'get':
                $request = INPUT_GET;
                break;
            case 'post':
                $request = INPUT_POST;
                break;
            default:
                return false;
        }
        foreach ($names as $name) {
            $input = filter_input($request, $name);
            if (is_null($input)) {
                return false;
            }
            $params[$name] = $input;
        }
        return $params;
    }
}
