<?php

namespace Spark\ActionPack\ViewHelper;

class Flash extends Base
{
    function get($type, array $default = [])
    {
        return $this->flashBag()->get($type, $default);
    }

    function flashBag()
    {
        return $this->application['session']->getFlashBag();
    }
}
