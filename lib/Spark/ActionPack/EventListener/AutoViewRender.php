<?php

namespace Spark\ActionPack\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;

use Spark\ActionPack\RenderPipeline;
use Spark\ActionPack\ViewContext;

class AutoViewRender implements EventSubscriberInterface
{
    protected $renderPipeline;
    protected $resolver;

    static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['renderView']
        ];
    }

    function __construct(RenderPipeline $render, ControllerClassResolver $resolver)
    {
        $this->renderPipeline = $render;
        $this->resolver = $resolver;
    }

    function renderView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $attributes = $request->attributes;

        $result = $event->getControllerResult();

        if (!empty($result)) {
            return new Response((string) $result);
        }

        if (!$attributes->get('spark.action_pack.autorender', true)) {
            return;
        }

        if (!$request->attributes->has('controller') and !$request->attributes->has('action')) {
            return;
        }

        $controllerName = $request->attributes->get('controller');
        $actionName = $request->attributes->get('action');

        $controller = $this->resolver->getController($controllerName);

        if (!$controller) {
            return;
        }

        $response = $controller->response();

        $response = $this->renderPipeline->render([
            'script' => "$controllerName/$actionName",
            'model' => $controller
        ], $response);

        $event->setResponse($response);
    }
}
