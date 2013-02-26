<?php

namespace Spark\ActionPack\ViewHelper;

abstract class Base
{
    protected $application;

    public function __construct(Application $app)
    {
        $this->application = $app;
    }
}
