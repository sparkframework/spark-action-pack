<?php

namespace Spark\ActionPack\ViewHelper;

use Silex\Application;
use Spark\ActionPack\View;

class Block extends Base
{
    protected $capturing;
    protected $blocks = [];

    function __construct(Application $app)
    {
        parent::__construct($app);

        $this->capturing = new \SplStack;
    }

    function __invoke($name)
    {
        return $this->get($name);
    }

    function get($name)
    {
        return $this->_get($this->context(), $name);
    }

    protected function _get(View\ViewContext $context, $name)
    {
        $blocks = $context->attributes->get('blocks', []);

        if ($content = @$blocks[$name]) {
            return $blocks[$name];
        }

        if (isset($context->parent)) {
            return $this->_get($context->parent, $name);
        }
    }

    function super($name)
    {
        $context = $this->context();

        if (isset($context->parent)) {
            return $this->_get($context->parent, $name);
        }
    }

    function set($name, $content, View\ViewContext $context = null)
    {
        if (null === $context) {
            $context = $this->context();
        }

        $blocks = $context->attributes->get('blocks', []);
        $blocks[$name] = $content;

        $context->attributes->set('blocks', $blocks);
    }

    function start($name)
    {
        $this->capturing->push($name);
        ob_start();
        return $this;
    }

    function end($name = null)
    {
        if ($name === null) {
            $name = $this->capturing->pop();
        }

        $content = ob_get_clean();
        $this->set($name, $content);

        return $this;
    }
}
