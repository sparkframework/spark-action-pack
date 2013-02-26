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

    function __construct()
    {
        $this->model = (object) [];
    }
}
