<?php

namespace Spark\ActionPack\View;

class RawHtmlStrategy extends RenderStrategy
{
    function onRender(RenderEvent $event)
    {
        if ($html = $event->getOption('html')) {
            $response = $event->getResponse();

            $response->setContent($html);
            $response->headers->set('Content-Type', 'text/html');

            $event->stopPropagation();
        }
    }
}
