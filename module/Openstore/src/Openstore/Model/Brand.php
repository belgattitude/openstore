<?php
namespace Openstore\Model;

use Openstore\Core\Model\AbstractModel;
use Openstore\Core\Model\BrowsableInterface;
use Openstore\Model\Browser\BrandBrowser;

class Brand extends AbstractModel implements BrowsableInterface
{
    /**
     * @return \Openstore\Model\Browser\BrandBrowser
     */
    public function getBrowser()
    {
        return new BrandBrowser($this);
    }
}
