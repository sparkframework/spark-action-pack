<?php

namespace Spark\ActionPack\ViewHelper;

class Render extends Base
{
    function __invoke($script, array $options = [])
    {
        return $this->render($script, $options);
    }

    function render($script, array $options = [])
    {
        $options = ['layout' => false];

        if (strpos($script, '/') !== false) {
            $partial = basename($script);
            $options['script'] = dirname($script) . "_$partial";
        } else {
            $controllerName = $this->application['request']->attributes->get('controller');
            $options['script'] = "$controllerName/_$script";
        }

        $render = $this->application['spark.render_pipeline'];

        if ($collection = @$options['collection']) {
            $returnValue = '';

            foreach ($collection as $entry) {
                $options['model'] = $entry;
                $returnValue .= $render->render($options)->getContent();
            }

            return $returnValue;
        }

        return $render->render($options)->getContent();
    }
}
