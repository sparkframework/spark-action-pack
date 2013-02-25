<?php

namespace Spark\ActionPack;

use Silex\Application;

class ActionPackServiceProvider implements \Silex\ServiceProviderInterface
{
    function register(Application $app)
    {
        $app['spark.action_pack.view_context'] = $app->share(function($app) {
            $class = isset($app['spark.action_pack.view_context_class'])
                ? $app['spark.action_pack.view_context_class']
                : '\\Spark\\Controller\\ViewContext';

            return new $class($app);
        });

        $app['spark.action_pack.controller_class_resolver'] = $app->share(function($app) {
            $resolver = new EventListener\ControllerClassResolver($app);

            return $resolver;
        });

        $app['spark.action_pack.render_pipeline'] = $app->share(function($app) {
            $render = new RenderPipeline($app['dispatcher'], $app['spark.action_pack.view_context'], $app['spark.action_pack.view_path']);
            $render->addStrategy(new View\JsonStrategy);
            $render->addStrategy(new View\TextStrategy);

            return $render;
        });

        $app["dispatcher"] = $app->extend("dispatcher", function($dispatcher, $app) {
            $dispatcher->addSubscriber($app['spark.action_pack.controller_class_resolver']);

            $dispatcher->addSubscriber(new EventListener\AutoViewRender(
                $app['spark.action_pack.render_pipeline'], $app['spark.action_pack.controller_class_resolver']
            ));

            return $dispatcher;
        });
    }

    function boot(Application $app)
    {
        $app->error(function(\Exception $e, $code) use ($app) {
            $renderPipeline = $app['spark.action_pack.render_pipeline'];

            if (isset($app['logger'])) {
                $app['logger']->addError($e);
            }

            if ($script = $renderPipeline->scriptPath->find("error/$code")) {
                $view = (object) [
                    'exception' => $e,
                    'code' => $code
                ];

                return $renderPipeline->render(['script' => "error/$code", 'model' => $view]);
            }
        });
    }
}
