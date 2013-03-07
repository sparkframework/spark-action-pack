<?php

namespace Spark\ActionPack\View;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

class ViewContext
{
    /** @var object Model which provides data for views */
    public $model;

    /** @var string Name of the view script */
    public $script;

    /** @var ViewContext Parent view, optional */
    public $parent;

    public $attributes;

    function __construct()
    {
        $this->model = (object) [];
        $this->attributes = new ParameterBag;
    }
}
