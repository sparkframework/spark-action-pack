<?php

namespace Spark\ActionPack\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;

use Spark\ActionPack\RenderPipeline;
use Spark\ActionPack\View;

class AutoViewRender implements EventSubscriberInterface
{
    protected $dispatcher;
    protected $resolver;
    protected $layout;

    static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['renderView']
        ];
    }

    function __construct(EventDispatcherInterface $dispatcher, $app)
    {
        $this->dispatcher = $dispatcher;
        $this->application = $app;
    }

    function renderView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $attributes = $request->attributes;

        $result = $event->getControllerResult();

        if (is_string($result)) {
            return new Response($result);
        }

        if (!$attributes->get('spark.action_pack.autorender', true)) {
            return;
        }

        if (!$request->attributes->has('controller') and !$request->attributes->has('action')) {
            return;
        }

        $controllerName = $request->attributes->get('controller');
        $actionName = $request->attributes->get('action');

        $controller = $this->application['spark.action_pack.controllers']->get($controllerName);

        if (!$controller) {
            return;
        }

        $context = new View\ViewContext;
        $context->script = "$controllerName/$actionName";
        $context->model = $controller;

        if ($request->attributes->get('spark.action_pack.render_layout', true)) {
            $context->parent = $this->application['spark.action_pack.layout'];
        }

        $renderEvent = new View\RenderEvent($context, []);
        $renderEvent->setResponse($controller->response());

        $this->dispatcher->dispatch(View\ViewEvents::RENDER, $renderEvent);

        if ($renderEvent->isPropagationStopped()) {
            $event->setResponse($renderEvent->getResponse());
        }
    }
}
