<?php

namespace Spark\ActionPack\ViewHelper;

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
        return @$this->blocks[$name];
    }

    function set($name, $content)
    {
        $this->blocks[$name] = $content;
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
        $this->blocks[$name] = $content;

        return $this;
    }
}
