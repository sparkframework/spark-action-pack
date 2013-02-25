<?php

namespace Spark\ActionPack\View;

use Symfony\Component\HttpFoundation;

class JsonStrategy extends RenderStrategy
{
    function onRender(RenderEvent $event)
    {
        if ($json = $event->getOption('json', false)) {
            $response = new HttpFoundation\JsonResponse(
                $json, $event->getOption('status', 200), $event->getOption('headers', [])
            );

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}
