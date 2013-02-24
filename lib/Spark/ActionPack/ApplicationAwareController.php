<?php

namespace Spark\ActionPack;

use Silex\Application;

interface ApplicationAwareController
{
    function setApplication(Application $application);
}
