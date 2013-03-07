<?php

namespace Spark\ActionPack\View;

use CHH\FileUtils;
use Pimple;
use MetaTemplate\Template;

class MetaTemplateStrategy extends RenderStrategy
{
    protected $scriptPath;
    protected $helpers;

    function __construct(FileUtils\PathStack $scriptPath, Pimple $helpers)
    {
        $this->scriptPath = $scriptPath;
        $this->helpers = $helpers;
    }

    function onRender(RenderEvent $event)
    {
        $context = $event->getContext();
        $dispatcher = $event->getDispatcher();

        if (empty($context->script)) {
            return;
        }

        $script = $this->scriptPath->find($context->script);

        if (!$script) {
            throw new \LogicException(sprintf(
                'Script "%s" not found in script path %s', $context->script,
                join(':', (array) $this->scriptPath->paths())
            ));
        }

        $template = Template::create($script);
        $response = $event->getResponse();

        if (is_callable([$template, 'getDefaultContentType']) and !$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', $template->getDefaultContentType());
        }

        # Unset the layout if the template is anything other than a template that rendered to HTML
        if ($response->headers->get('Content-Type') !== "text/html") {
            $viewContext->parent = null;
        }

        $helpers = $this->helpers;
        $helpers['view_context'] = $context;

        $vars['_helper'] = $vars['_h'] = $helpers;
        $vars['context'] = $context;

        $content = $template->render($context->model, $vars);

        if (isset($context->parent)) {
            $context->parent->attributes->add($context->attributes->all());

            $blocks = $helpers['block'];
            $blocks->set('content', $content, $context->parent);

            $parentEvent = new RenderEvent($context->parent, []);
            $dispatcher->dispatch(ViewEvents::RENDER, $parentEvent);

            $content = $parentEvent->getResponse()->getContent();
        }

        $response->setContent($content);

        $event->stopPropagation();
    }
}
