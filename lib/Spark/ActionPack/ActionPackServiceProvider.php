<?php

namespace Spark\ActionPack;

use Silex\Application;
use CHH\FileUtils\PathStack;

class ActionPackServiceProvider implements \Silex\ServiceProviderInterface
{
    function register(Application $app)
    {
        $app['spark.action_pack.view_context'] = function() {
            return new View\ViewContext;
        };

        $app['spark.action_pack.layout'] = $app->share(function() use ($app) {
            $layout = $app['spark.action_pack.view_context'];
            $layout->script = "default";

            return $layout;
        });

        $app['spark.action_pack.controller_class_resolver'] = $app->share(function($app) {
            $resolver = new EventListener\ControllerClassResolver($app);

            return $resolver;
        });

        $app['spark.action_pack.view.script_path'] = $app->share(function() use ($app) {
            $path = new PathStack();
            $path->appendPaths($app['spark.action_pack.view_path']);

            return $path;
        });

        $app['spark.action_pack.view.helpers'] = $app->share(function() use ($app) {
            $helpers = new \Pimple;

            $helpers['app'] = $app;

            $helpers['asset'] = $helpers->share(function() use ($app) {
                return new ViewHelper\Asset($app);
            });

            $helpers['flash'] = $helpers->share(function() use ($app) {
                return new ViewHelper\Flash($app);
            });

            $helpers['render'] = $helpers->share(function() use ($app) {
                return new ViewHelper\Render($app);
            });

            $helpers['block'] = $helpers->share(function() use ($app) {
                return new ViewHelper\Block($app);
            });

            $helpers['path'] = $helpers->share(function() use ($app) {
                return new ViewHelper\Path($app);
            });

            return $helpers;
        });

        $app["dispatcher"] = $app->extend("dispatcher", function($dispatcher, $app) {
            $dispatcher->addSubscriber($app['spark.action_pack.controller_class_resolver']);

            $dispatcher->addSubscriber(new EventListener\AutoViewRender(
                $dispatcher,
                $app['spark.action_pack.controller_class_resolver'],
                $app['spark.action_pack.layout']
            ));

            # Register default view rendering strategies
            $dispatcher->addSubscriber(new View\JsonStrategy);
            $dispatcher->addSubscriber(new View\TextStrategy);
            $dispatcher->addSubscriber(new View\RawHtmlStrategy);

            return $dispatcher;
        });
    }

    function boot(Application $app)
    {
        $app->error(function(\Exception $e, $code) use ($app) {
            if (isset($app['monolog']) and null !== $app['monolog']) {
                $app['monolog']->addError($e);
            }

            $scriptPath = $app['spark.action_pack.view.script_path'];

            if ($scriptPath->find("error/$code")) {
                $view = (object) [
                    'exception' => $e,
                    'code' => $code
                ];

                $context = $app['spark.action_pack.view_context'];
                $context->script = "error/$code";
                $context->model = $view;

                $event = new View\RenderEvent($context, []);
                $app['dispatcher']->dispatch(View\ViewEvents::RENDER, $event);

                if ($event->isPropagationStopped()) {
                    return $event->getResponse();
                }
            }
        });
    }
}
