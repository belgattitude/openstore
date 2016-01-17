<?php

namespace Openstore\Model;

use Openstore\Core\Model\AbstractModel;
use Openstore\Core\Model\BrowsableInterface;
use Openstore\Model\Browser\ProductTranslationBrowser;

class ProductTranslation extends AbstractModel implements BrowsableInterface
{
    /**
     * @return \Openstore\Model\Browser\ProductTranslationBrowser
     */
    public function getBrowser()
    {
        return new ProductTranslationBrowser($this);
    }
}
