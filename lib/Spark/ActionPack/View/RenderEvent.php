<?php

namespace Spark\ActionPack\View;

use Symfony\Component\EventDispatcher;
use Symfony\Component\HttpFoundation;

class RenderEvent extends EventDispatcher\Event
{
    protected $options;
    protected $response;

    function __construct(ViewContext $context, array $options)
    {
        $this->context = $context;
        $this->options = $options;
    }

    function getContext()
    {
        return $this->context;
    }

    function getOption($option, $defaultValue = null)
    {
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        return $defaultValue;
    }

    function getOptions()
    {
        return $this->options;
    }

    function getResponse()
    {
        if (null === $this->response) {
            $this->response = new HttpFoundation\Response;
        }

        return $this->response;
    }

    function setResponse(HttpFoundation\Response $response)
    {
        $this->response = $response;

        return $this;
    }
}
