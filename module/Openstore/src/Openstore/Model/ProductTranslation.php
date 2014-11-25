<?php

namespace Openstore\Model;

use Openstore\Core\Model\AbstractModel;
use Openstore\Core\Model\BrowsableInterface;
use Openstore\Model\Browser\ProductTranslationBrowser;
use Soluble\Normalist\SyntheticTable;

class ProductTranslation extends AbstractModel implements BrowsableInterface {

    /**
     * @return \Openstore\Model\Browser\ProductTranslationBrowser
     */
    function getBrowser() {
        return new ProductTranslationBrowser($this);
    }


}
