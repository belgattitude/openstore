<?php

namespace Openstore\Model;

use Openstore\Core\Model\AbstractModel;
use Openstore\Core\Model\BrowsableInterface;
use Openstore\Model\Browser\ProductBrowser;
use Soluble\Normalist\SyntheticTable;

class Product extends AbstractModel implements BrowsableInterface
{
    /**
     * @return \Openstore\Model\Browser\ProductBrowser
     */
    public function getBrowser()
    {
        return new ProductBrowser($this);
    }

    /**
     *
     * @param int $product_id
     * @param int $pricelist_id
     * @param int $customer_id
     * @param string $language
     * @return \ArrayObject|false
     */
    public function getInfo($product_id, $pricelist_id, $customer_id = null, $language = '')
    {
        $service = $this->serviceLocator->get('Openstore\Service');
        $productModel = $service->getModel('Model\Product');


        $st = new SyntheticTable($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        $pricelist = $st->find('pricelist', $pricelist_id);
        if (!$st) {
            throw new \Exception("Cannot find pricelist '$pricelist_id'");
        }

        $product = $this->getBrowser()->setSearchParams(
            [
                                    'id' => $product_id,
                                    'language' => $language,
                                    'pricelist' => $pricelist['reference'],
                                ]
        )
                                ->getStore()->getData()->current();
                                return $product;
    }
}
