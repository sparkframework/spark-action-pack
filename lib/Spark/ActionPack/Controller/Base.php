<?php

namespace Spark\ActionPack\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Spark\ActionPack\ApplicationAwareController;
use Spark\ActionPack\ActionHelper;

abstract class Base implements ApplicationAwareController
{
    use ActionHelper\Filters;
    use ActionHelper\Redirect;
    use ActionHelper\Layout;

    protected $application;

    private $response;
    private $flash;

    # Public: Map formats to callback functions.
    #
    # Example
    #
    #   $this->respondTo([
    #       'html' => function() {
    #           return $this->render();
    #       },
    #       'json' => function() {
    #           return $this->render(['json' => $this->model->toArray()]);
    #       }
    #   ]);
    function respondTo(array $spec)
    {
        $request = $this->request();

        # Allow format override via a special '_format' request parameter
        if ($formatOverride = $request->getRequestFormat(null)) {
            if (isset($spec[$formatOverride])) {
                return $spec[$formatOverride]();
            }
        }

        # Find the best match for the requested formats in the defined format handlers
        foreach ($request->getAcceptableContentTypes() as $contentType) {
            $format = $request->getFormat($contentType);

            if (isset($spec[$format])) {
                return $spec[$format]();
            }
        }

        $this->notFound();
    }

    function render($options = [])
    {
        $attributes = $this->request()->attributes;

        if (!$options) {
            $options['script'] = $attributes->get('controller') . '/' . $attributes->get('action');
        }

        if (is_string($options)) {
            $script = $options;
            $options = ['script' => $script];
        }

        if (isset($options['status'])) {
            $this->response()->setStatusCode($options['status']);
            unset($options['status']);
        }

        if (isset($options['response'])) {
            $response = $options['response'];
            unset($options['response']);
        } else {
            $response = $this->response();
        }

        return $this->application['spark.render_pipeline']->render($options, $response);
    }

    function rescue($exceptionClass, $method)
    {
        if (is_callable([$this, $method])) {
            $this->application->error($exceptionClass, [$this, $method]);
        } else {
            $this->application->error($exceptionClass, $method);
        }
    }

    function notFound($message = '')
    {
        return $this->application->abort(404, $message);
    }

    function request()
    {
        return $this->application['request'];
    }

    function response()
    {
        return $this->response ?: $this->response = new Response;
    }

    function flash()
    {
        return $this->flash ?: $this->flash = $this->application['session']->getFlashBag();
    }

    function session()
    {
        return $this->application['session'];
    }

    function application()
    {
        return $this->application;
    }

    function setApplication(Application $application)
    {
        $this->application = $application;
    }
}
