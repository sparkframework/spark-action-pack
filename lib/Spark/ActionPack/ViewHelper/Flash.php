<?php

namespace Spark\ActionPack\ViewHelper;

trait Flash
{
    function flash()
    {
        return $this->application['session']->getFlashBag();
    }
}
