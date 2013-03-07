<?php

namespace Spark\ActionPack\ViewHelper;

use Silex\Application;

abstract class Base
{
    protected $application;

    public function __construct(Application $app)
    {
        $this->application = $app;
    }

    protected function helper($helper)
    {
        return $this->application['spark.action_pack.view.helpers'][$helper];
    }

    protected function context()
    {
        return $this->application['spark.action_pack.view.helpers']['view_context'];
    }
}
