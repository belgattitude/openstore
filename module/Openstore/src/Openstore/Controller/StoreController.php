<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Openstore\Controller;

use Openstore\Entity;
use Openstore\Catalog\Browser\ProductFilter;
use Openstore\Permission;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Openstore\Catalog\Helper\SearchParams;

class StoreController extends AbstractActionController
{
    /**
     *
     * @var \Openstore\Options
     */
    protected $config;
    protected $adapter;

    /**
     *
     * @var \Openstore\Permission
     */
    protected $permission;

    /**
     *
     * @var \Openstore\Service
     */
    protected $service;


    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $sl = $this->getServiceLocator();
        $this->adapter            = $sl->get('Zend\Db\Adapter\Adapter');
        $this->service            = $sl->get('Openstore\Service');
        $this->configuration    = $sl->get('Openstore\Configuration');
    //	$this->permission		= $sl->get('Openstore\Permission');
        //var_dump($this->permission);
        //die();
        parent::onDispatch($e);
    }

    public function indexAction()
    {
    }

    public function productAction()
    {
        $pricelist = $this->params()->fromRoute('pricelist');
        $language = $this->params()->fromRoute('ui_language');

        $view = new ViewModel();
        //$searchParams = SearchParams::createFromRequest();
        $this->layout()->search_keywords = '';

        // Include product browser

        $product = $this->service->getModel('Model\Product');
        $productBrowser = $product->getBrowser()->setSearchParams(
            [
                                'id'         => $this->params()->fromRoute('product_id'),
                                'language'     => $language,
                                'pricelist'  => $pricelist,
                            ]
        );

                            $product = $productBrowser->getStore()->getData()->current();
                            $view->product = $product;


        /**
         * Assign other items
         */
                            $searchParams = new SearchParams();
                            $searchParams->setServiceLocator($this->getServiceLocator());
                            $searchParams->setLanguage($language);
                            $searchParams->setPricelist($pricelist);
                            $searchParams->setBrands($product->brand_reference);
                            $searchParams->setCategories($product->category_reference);

                            $browser_items = $this->getBrowserItems($searchParams);
                            $view->categories = $browser_items['categories'];
                            $view->brands = $browser_items['brands'];

        // Setting other variables
                            $view->searchParams = $searchParams;

                            $category = $this->service->getModel('Model\Category');
                            $view->category_breadcrumb = $category->getAncestors($searchParams->getFirstCategory(), $language);

                            return $view;
    }

    /**
     * @return \Soluble\Normalist\Synthetic\TableManager
     */
    protected function getTableManager()
    {
        return $this->getServiceLocator()->get('SolubleNormalist\TableManager');
    }

    public function browseAction()
    {
        $tm = $this->getTableManager();
        $tm->table('product');
        $tm = $this->getTableManager();
        //$rec = $tm->table('product')->findOrFail(1);
        //var_dump($rec->toArray());
        //die();


        $view = new ViewModel();
        $searchParams = SearchParams::createFromRequest($this->params(), $this->getServiceLocator());
        $pricelist = $searchParams->getPricelist();
        $language = $searchParams->getLanguage();


        $this->layout()->search_keywords = $searchParams->getQuery();

        $browserItems = $this->getBrowserItems($searchParams);
        $view->categories = $browserItems['categories'];
        $view->brands = $browserItems['brands'];


        /**
         * Product Browser
         */
        $product = $this->service->getModel('Model\Product');
        $productBrowser = $product->getBrowser()->setSearchParams(
            [
                                'query'         => $searchParams->getQuery(),
                                'language'   => $language,
                                'pricelist'  => $pricelist,
                                'brands'     => $searchParams->getBrands(),
                                'categories' => $searchParams->getCategories()
                            ]
        )
                            ->setLimit($searchParams->getLimit(), $searchParams->getOffset())
                            ->addFilter($searchParams->getFilter());

                            $view->products = $productBrowser->getStore()->getData();

        /**
         * Breadcrumb
         */
                            $category = $this->service->getModel('Model\Category');
                            $view->category_breadcrumb = $category->getAncestors($searchParams->getFirstCategory(), $language);

        // Setting other variables
                            $view->searchParams = $searchParams;

                            return $view;
    }


    protected function getBrowserItems($searchParams)
    {
        $items = array();


        $language = $searchParams->getLanguage();
        $pricelist = $searchParams->getPricelist();


        /**
         * Category browser
         */
        $category = $this->service->getModel('Model\Category');
        $categoryBrowser = $category->getBrowser()->setSearchParams(
            [
                                'language'     => $language,
                                'pricelist'  => $pricelist,
                                'brands'     => $searchParams->getBrands()
                            ]
        )
                            ->setOption('depth', 1)
                            ->setOption('include_empty_nodes', false)
                            ->setOption('expanded_category', $searchParams->getFirstCategory())
                            ->addFilter($searchParams->getFilter());

                            $items['categories'] = $categoryBrowser->getStore()->getData();

        /**
         * Brand browser
         */
                            $brand = $this->service->getModel('Model\Brand');
                            $brandBrowser = $brand->getBrowser()->setSearchParams(
                                [
                                'language'     => $language,
                                'pricelist'  => $pricelist,
                                ]
                            )
                            ->addFilter($searchParams->getFilter());

                            $items['brands'] = $brandBrowser->getStore()->getData();

                            return $items;
    }
}
