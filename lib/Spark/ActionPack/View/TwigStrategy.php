<?php

namespace Spark\ActionPack\View;

class TwigStrategy extends RenderStrategy
{
    protected $twig;

    function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    function onRender(RenderEvent $event)
    {
        $context = $event->getContext();
        $response = $event->getResponse();

        try {
            $template = $this->twig->loadTemplate($context->script);
        } catch (\Twig_Error_Loader $e) {
            return;
        }

        $response->setContent($template->render((array) $context->model));

        $event->stopPropagation();
    }
}
