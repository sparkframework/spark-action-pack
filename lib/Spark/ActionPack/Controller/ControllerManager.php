<?php

namespace Spark\ActionPack\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Spark\ActionPack\ApplicationAwareController;

class ControllerManager
{
    protected $controllers = [];
    protected $modules = [];
    protected $defaultModule = "default";
    protected $application;

    function __construct($application)
    {
        $this->application = $application;
    }

    function registerModule($module, $namespace)
    {
        $this->modules[$module] = $namespace;
        return $this;
    }

    function setDefaultModule($module)
    {
        $this->defaultModule = $module;
    }

    function processRequest(Request $request)
    {
        $current = $request->attributes->get('_controller');
        $moduleName = $request->attributes->get('module', $this->defaultModule);

        # If controller is already callable, then we don't need to do anything
        if (is_callable($current)) {
            return;
        }

        if ((!is_string($current) and false === strpos($current, '#')) and !$request->attributes->has('controller')) {
            return;
        }

        if (false !== strpos($current, '#')) {
            list($controllerName, $actionName) = explode('#', $current);

            if (false !== strpos($controllerName, '::')) {
                list($moduleName, $controllerName) = explode('::', $controllerName);
            }
        } elseif ($request->attributes->has('controller')) {
            $controllerName = $request->attributes->get('controller');
            $actionName = $request->attributes->get('action');
        }

        $route = $this->application['routes']->get($request->attributes->get('_route'));
        $action = $this->camelize($actionName, false) . "Action";

        $controller = $this->get($controllerName, $moduleName);

        if (null === $controller) {
            return;
        }

        $request->attributes->set('action', $actionName);
        $request->attributes->set('controller', $controllerName);

        if (is_callable([$controller, "onBeforeFilter"])) {
            $route->before([$controller, "onBeforeFilter"]);
        }

        if (is_callable([$controller, "onAfterFilter"])) {
            $route->after([$controller, "onAfterFilter"]);
        }

        if (is_callable([$controller, $action])) {
            $request->attributes->set('_controller', [$controller, $action]);
        } else {
            $request->attributes->set('_controller', null);
        }
    }

    protected function camelize($string, $upperCaseFirst = true)
    {
        $camelized = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

        if (!$upperCaseFirst) {
            $camelized = lcfirst($camelized);
        }

        return $camelized;
    }

    function get($name, $module = null)
    {
        if (null === $module) {
            $module = $this->defaultModule;
        }

        if (!isset($this->modules[$module])) {
            return;
        }

        $namespace = $this->modules[$module];

        if (class_exists($name)) {
            $class = $name;
        } else {
            $class = rtrim($namespace, '\\') . '\\' . $this->camelize($name) . "Controller";

            if (!class_exists($class)) {
                return;
            }
        }

        if (isset($this->controllers[$class])) {
            $controller = $this->controllers[$class];
        } else {
            $controller = new $class;

            if ($controller instanceof ApplicationAwareController or is_callable([$controller, "setApplication"])) {
                $controller->setApplication($this->application);
            }

            $this->controllers[$class] = $controller;
        }

        return $controller;
    }
}
