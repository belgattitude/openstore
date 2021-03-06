<?php

namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Sql\Expression;
use Openstore\Catalog\Helper\SearchParams;
use Soluble\FlexStore\Writer\JsonWriter;

class SearchController extends AbstractActionController
{
    /**
     *
     * @var Openstore\Options
     */
    protected $config;

    /**
     *
     * @var Zend\Db\Adapter\Adapter
     */
    protected $adapter;


    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        //$this->config	= $this->getServiceLocator()->get('Openstore\Config');
        $this->adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        parent::onDispatch($e);
    }


    public function indexAction()
    {
        die('searchcontroller');
    }



    public function productAction()
    {
        $searchParams = SearchParams::createFromRequest($this->params(), $this->getServiceLocator());

        $product = $this->getServiceLocator()->get('Openstore\Service')->getModel('Model\Product');
        $browser = $product->getBrowser()->setSearchParams(
            [
                                'query'     => $searchParams->getQuery(),
                                'pricelist' => $searchParams->getPricelist(),
                                'language'  => $searchParams->getLanguage()
                            ]
        )
                            ->setLimit(20, $offset = 0)
                            ->setColumns(
                                [
                                    'product_id'        => new Expression('p.product_id'),
                                    'reference'        => new Expression('p.reference'),
                                    'display_reference'     => new Expression('COALESCE(p.display_reference, p.reference)'),
                                    'brand_title'        => new Expression('pb.title'),
                                    'category_reference'    => new Expression('pc.reference'),
                                    'category_title'    => new Expression('COALESCE(pc18.title, pc.title)'),
                                    'title'            => new Expression('COALESCE(p18.title, p.title)'),
                                    'invoice_title'        => new Expression('COALESCE(p18.invoice_title, p.invoice_title)'),
                                    'picture_media_id'    => new Expression('pm.media_id'),
                                ]
                            )
                        ->addFilter($searchParams->getFilter());

        //var_dump(get_class($browser)); die();
        $store = $browser->getStore();


        $writer = new JsonWriter($store);
                //$writer->setDebug(true);
        $json = $writer->send();
        die();
    }


    public function brandAction()
    {
        $searchParams = SearchParams::createFromRequest($this->params(), $this->getServiceLocator());

        $brand = $this->getServiceLocator()->get('Openstore\Service')->getModel('Model\Brand');
        $browser = $brand->getBrowser()->setSearchParams(
            [
                                'query' => $searchParams->getQuery(),
                                'pricelist' => $searchParams->getPricelist(),
                                'language' => $searchParams->getLanguage()
                            ]
        )
                            ->setLimit(20, $offset = 0)
                            ->addFilter($searchParams->getFilter());

        $store = $browser->getStore();

        $writer = new JsonWriter($store);
        $json = $writer->send();
        die();
    }
}
