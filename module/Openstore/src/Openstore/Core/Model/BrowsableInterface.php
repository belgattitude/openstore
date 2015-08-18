<?php

namespace Openstore\Core\Model;

interface BrowsableInterface
{
    /**
     * @return \Openstore\Core\Model\Browser\AbstractBrowser
     */
    public function getBrowser();
}
