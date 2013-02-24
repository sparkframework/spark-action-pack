<?php

namespace Spark\Controller;

use Silex\Application;

interface ApplicationAwareController
{
    function setApplication(Application $application);
}
