<?php

namespace Spark\ActionPack\ViewHelper;

class Asset extends Base
{
    function link($logicalPath)
    {
        return $this->application['pipe']->assetLink($logicalPath);
    }

    function linkTag($logicalPath)
    {
        return $this->application['pipe']->assetLinkTag($logicalPath);
    }
}
