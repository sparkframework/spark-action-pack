<?php

namespace Spark\ActionPack;

use Silex\Application;

class ActionPackServiceProvider implements \Silex\ServiceProviderInterface
{
    function register(Application $app)
    {
        $app['spark.action_pack.view_context'] = $app->share(function($app) {
            $class = isset($app['spark.view_context_class'])
                ? $app['spark.view_context_class']
                : '\\Spark\\Controller\\ViewContext';

            return new $class($app);
        });

        $app['spark.action_pack.controller_class_resolver'] = $app->share(function($app) {
            $resolver = new EventListener\ControllerClassResolver($app);
            return $resolver;
        });

        $app['spark.action_pack.render_pipeline'] = $app->share(function($app) {
            $render = new RenderPipeline($app['spark.view_context'], $app['spark.view_path']);

            return $render;
        });

        $app["dispatcher"] = $app->extend("dispatcher", function($dispatcher, $app) {
            $dispatcher->addSubscriber($app['spark.controller_class_resolver']);

            $dispatcher->addSubscriber(new EventListener\AutoViewRender(
                $app['spark.render_pipeline'], $app['spark.controller_class_resolver']
            ));

            return $dispatcher;
        });
    }

    function boot(Application $app)
    {
        $app->error(function(\Exception $e, $code) use ($app) {
            $renderPipeline = $app['spark.action_pack.render_pipeline'];

            if (!empty($app['logger'])) {
                $app['logger']->addError($e);
            }

            if ($script = $renderPipeline->scriptPath->find("error/$code")) {
                $context = (object) [
                    'exception' => $e,
                    'code' => $code
                ];

                return $renderPipeline->render(['script' => "error/$code", 'context' => $context]);
            }
        });
    }
}
