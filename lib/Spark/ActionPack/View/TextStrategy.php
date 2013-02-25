<?php

namespace Spark\ActionPack\View;

use Symfony\Component\HttpFoundation;

class JsonStrategy extends RenderStrategy
{
    function onRender(RenderEvent $event)
    {
        if ($text = $event->getOption('text', false)) {
            $response = $event->getResponse();
            $response->setContent($text);
            $response->headers->set('Content-Type', 'text/plain');
            $response->headers->add($event->getOption('headers', []));

            if ($status = $event->getOption('status')) {
                $response->setStatusCode($status);
            }

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}
