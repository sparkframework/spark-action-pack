<?php

namespace Spark\ActionPack\ViewHelper;

use Spark\ActionPack\View;

class Render extends Base
{
    function __invoke($script, array $options = [])
    {
        return $this->render($script, $options);
    }

    function render($script, array $options = [])
    {
        $helpers = $this->application['spark.action_pack.view.helpers'];
        $context = clone $helpers['view_context'];
        unset($context->parent);

        if (strpos($script, '/') !== false) {
            $partial = basename($script);
            $context->script = dirname($script) . "_$partial";
        } else {
            $controllerName = $this->application['request']->attributes->get('controller');
            $context->script = "$controllerName/_$script";
        }

        if ($collection = @$options['collection']) {
            $returnValue = '';

            foreach ($collection as $item) {
                $c = clone $context;
                $c->model = $item;

                $returnValue .= $this->renderContext($c);
            }

            return $returnValue;
        }

        return $this->renderContext($context);
    }

    protected function renderContext(View\ViewContext $context, array $options = [])
    {
        $event = new View\RenderEvent($context, $options);
        $this->application['dispatcher']->dispatch(View\ViewEvents::RENDER, $event);

        return $event->getResponse()->getContent();
    }
}
