<?php

namespace Spark\ActionPack;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Defines more convenience methods for defining routes
 *
 * Examples:
 *
 *     <?php
 *
 *     $app['controllers']->draw(function($routes) {
 *         # Defines set of routes to manipulate a collection of posts.
 *         $routes->resources('posts');
 *
 *         # Scope all routes defined using the callback to the given
 *         # prefix.
 *         $routes->scope('/admin', function($admin) {
 *             $admin->get('/', 'admin#index');
 *         });
 *     });
 *
 */
class ControllerCollection extends \Silex\ControllerCollection
{
    protected $scopes = [];

    /**
     * Scopes all routes in the collection to the given path
     *
     * Example:
     *
     *     <?php
     *
     *     $routes->scope('/admin', function($admin) {
     *         $admin->match('/', 'admin::index#index');
     *     });
     *
     * @param string $prefix
     * @param callable $callback Callback which gets invoked with the collection
     */
    function scope($prefix, callable $callback)
    {
        $routes = new static($this->defaultRoute);

        $callback($routes);

        $this->scopes[$prefix] = $routes;

        return $this;
    }

    /**
     * Invokes the callback with the collection as argument
     *
     * @param callable $callback
     *
     * @return ControllerCollection
     */
    function draw(callable $callback)
    {
        $callback($this);

        return $this;
    }

    /**
     * Returns a controller which redirects to the specified URL.
     *
     * Example:
     *
     *     <?php
     *     $routes->match('/foo', $routes->redirect('/bar'));
     *
     * @param string $url
     * @param array $options
     *
     * @return \Closure
     */
    function redirect($to, $options = [])
    {
        return function(\Silex\Application $app) use ($to, $options) {
            $headers  = (array) @$options['headers'];
            $status   = @$options['status'] ?: 302;
            $params   = (array) @$options['params'];
            $absolute = @$options['absolute'] ?: false;

            # Generate URL if the first argument is an existing route name
            if ($route = $app['routes']->get($to)) {
                $to = $app['url_generator']->generate($to, $params, $absolute);
            }

            return new RedirectResponse($to, $status, $headers);
        };
    }

    /**
     * Define routes for a collection of resources.
     *
     * Example:
     *
     *     <?php
     *
     *     $router->resources('posts');
     *
     * This defines the following routes, for the resource 'posts':
     *
     * | Route                   | Action        |
     * | ----------------------- | ------------- |
     * | GET    /posts           | posts#index   |
     * | GET    /posts/new       | posts#new     |
     * | GET    /posts/{id}      | posts#show    |
     * | GET    /posts/{id}/edit | posts#edit    |
     * | POST   /posts           | posts#create  |
     * | PUT    /posts/{id}      | posts#update  |
     * | DELETE /posts/{id}      | posts#delete  |
     *
     * @param string $resourceName
     * @param array $options
     *
     * @return ControllerCollection
     */
    function resources($resourceName, $options = [])
    {
        $controller = @$options['controller'] ?: $resourceName;

        $this->get("/$resourceName", "$controller#index")
             ->bind("{$resourceName}_index");

        $this->get("/$resourceName/new", "$controller#new")
             ->bind("{$resourceName}_new");

        $this->get("/$resourceName/{id}", "$controller#show")
             ->bind("{$resourceName}_show");

        $this->get("/$resourceName/{id}/edit", "$controller#edit")
             ->bind("{$resourceName}_edit");

        $this->post("/$resourceName", "$controller#create")
             ->bind("{$resourceName}_create");

        $this->put("/$resourceName/{id}", "$controller#update")
             ->bind("{$resourceName}_update");

        $this->delete("/$resourceName/{id}", "$controller#destroy")
             ->bind("{$resourceName}_destroy");

        return $this;
    }

    /**
     * Define resource routes for a singular resource (a resource where
     * there can be only one of it)
     *
     * Example:
     *
     *     <?php
     *     $router->resource('profile');
     *
     * This defines the following routes for a resource "profile":
     *
     * | Route                | Action         |
     * | -------------------- | -------------- |
     * | GET    /profile      | profile#show   |
     * | GET    /profile/new  | profile#new    |
     * | GET    /profile/edit | profile#edit   |
     * | POST   /profile      | profile#create |
     * | PUT    /profile      | profile#update |
     * | DELETE /profile      | profile#delete |
     *
     * @param string $resourceName
     * @param array $options
     *
     * @return ControllerCollection
     */
    function resource($resourceName, $options = [])
    {
        $controller = @$options['controller'] ?: $resourceName;

        $this->get("/$resourceName", "$controller#show");
        $this->get("/$resourceName/new", "$controller#new");
        $this->get("/$resourceName/edit", "$controller#edit");
        $this->post("/$resourceName", "$controller#create");
        $this->put("/$resourceName", "$controller#update");
        $this->delete("/$resourceName", "$controller#destroy");

        return $this;
    }

    function flush($prefix = '')
    {
        $routeCollection = parent::flush($prefix);

        foreach ($this->scopes as $scope => $controllerCollection) {
            $routeCollection->addCollection($controllerCollection->flush($scope), $scope);
        }

        return $routeCollection;
    }
}
