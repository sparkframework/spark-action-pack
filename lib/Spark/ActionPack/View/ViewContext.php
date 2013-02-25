<?php

namespace Spark\ActionPack\View;

use Symfony\Component\HttpFoundation\Response;

class ViewContext
{
    /** @var object Model which provides data for views */
    public $model;

    /** @var string Name of the view script */
    public $script;

    /** @var ViewContext Parent context, if available */
    public $parent;

    /** @var Application */
    protected $application;

    function __construct(\Silex\Application $app)
    {
        $this->application = $app;
        $this->model = (object) [];
    }
}

