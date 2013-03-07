<?php

namespace Spark\ActionPack;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher;
use Spark\ActionPack\View;
use CHH\FileUtils\PathStack;

class RenderPipeline
{
    public $formats = [
        'json' => 'application/json',
        'html' => 'text/html',
        'text' => 'text/plain',
        'xml' => 'application/xml'
    ];

    /**
     * @var PathStack
     */
    public $scriptPath;

    /**
     * Event Dispatcher
     * @var EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    /**
     * View Context Prototype
     */
    protected $defaultContext;

    /**
     * Constructor
     *
     * @param EventDispatcher\EventDispatcher $dispatcher
     * @param ViewContext $defaultContext
     * @param array $scriptPath Array of lookup paths for view scripts
     */
    function __construct(EventDispatcher\EventDispatcherInterface $dispatcher, View\ViewContext $defaultContext, $scriptPath = null)
    {
        $this->dispatcher = $dispatcher;
        $this->defaultContext = $defaultContext;
    }

    function addStrategy($strategy)
    {
        if (is_callable($strategy)) {
            $this->dispatcher->addListener(View\ViewEvents::RENDER, $strategy);
        } else if ($strategy instanceof EventDispatcher\EventSubscriberInterface) {
            $this->dispatcher->addSubscriber($strategy);
        } else {
            throw new \InvalidArgumentException("Strategy must be either a callable or"
                . " implement Symfony\\Component\\EventDispatcher\\EventSubscriberInterface");
        }

        return $this;
    }

    function renderContext(View\ViewContext $context, array $options = [], Response $response = null)
    {
        $event = new View\RenderEvent($context, $options);

        if (null !== $response) {
            $event->setResponse($response);
        }

        $this->dispatcher->dispatch(View\ViewEvents::RENDER, $event);

        return $event->getResponse();
    }

    function render($options = [], Response $response = null)
    {
        $context = $this->createContext();

        if ($this->renderLayout and @$options['layout'] !== false) {
            $context->parent = $this->layout;
        }

        $context->script = @$options['script'];
        $context->model = @$options['model'] ?: new \stdClass;

        return $this->renderContext($context, $options, $response);
    }

    protected function createContext()
    {
        return clone $this->defaultContext;
    }
}
