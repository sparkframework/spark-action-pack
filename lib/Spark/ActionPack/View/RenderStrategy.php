<?php

namespace Spark\ActionPack\View;

use Symfony\Component\EventDispatcher;

abstract class RenderStrategy implements EventDispatcher\EventSubscriberInterface
{
    protected static $priority = 0;

    static function getSubscribedEvents()
    {
        return [
            ViewEvents::RENDER => ['onRender', static::getPriority()]
        ];
    }

    static function getPriority()
    {
        return static::$priority;
    }

    abstract function onRender(RenderEvent $event);
}
