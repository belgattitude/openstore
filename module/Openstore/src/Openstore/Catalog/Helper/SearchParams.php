<?php

namespace Openstore\Catalog\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

// SHOULD BE PAGEContext UserContext ?
class SearchParams
{
    /**
     * @var ArrayObject
     */
    protected $params;

    /**
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    public function __construct()
    {
        $this->params = new \ArrayObject();
    }

    /**
     *
     * @param \Openstore\Controller\Zend\Mvc\Controller\Plugin\Params $params
     * @return \Openstore\Controller\searchParams
     */
    public static function createFromRequest(\Zend\Mvc\Controller\Plugin\Params $params, ServiceLocatorInterface $serviceLocator)
    {
        $searchParams = new SearchParams();
        $searchParams->setServiceLocator($serviceLocator);

        if (($filter = $params->fromRoute('filter')) == '') {
            $filter = 'all';
        }
        $searchParams->setFilter($filter);
        //var_dump($params->fromRoute('categories')); die();
        $categories = $params->fromRoute('categories');
        if (trim($categories) == '') {
            $searchParams->setCategories(null);
        } else {
            $searchParams->setCategories(explode(',', $categories));
        }


        $brands = $params->fromRoute('brands');
        if (trim($brands) == '') {
            $searchParams->setBrands(null);
        } else {
            $searchParams->setBrands(explode(',', $brands));
        }

        $searchParams->setQuery($params->fromQuery('query', ''));
        //var_dump($searchParams->getQuery()); die('ggg');
        $searchParams->setLimit($params->fromRoute('perPage', 20));
        $searchParams->setPage($params->fromRoute('page', 1));
        $searchParams->setSortBy($params->fromRoute('sortBy'));
        $searchParams->setSortDir($params->fromRoute('sortDir', 'ASC'));
        $searchParams->setLanguage($params->fromRoute('ui_language', 'en'));
        $searchParams->setPricelist($params->fromRoute('pricelist'));

        return $searchParams;
    }

    /**
     *
     * @return \ArrayObject
     */
    public function toArray()
    {
        return $this->params;
    }

    public function setLanguage($language)
    {
        $this->params['language'] = $language;
        return $this;
    }

    public function getLanguage()
    {
        return $this->params['language'];
    }

    public function setPricelist($pricelist)
    {
        $this->params['pricelist'] = $pricelist;
        return $this;
    }

    public function getPricelist()
    {
        return $this->params['pricelist'];
    }

    /**
     *
     * @param string $query
     * @return \Openstore\Controller\searchParams
     */
    public function setQuery($query)
    {
        $this->params['query'] = $query;

        return $this;
    }

    public function getQuery()
    {
        return $this->params['query'];
    }

    public function setCategories($categories)
    {
        $categories = (array) $categories;

        if (count($categories) == 0) {
            $this->params['categories'] = null;
        } else {
            $this->params['categories'] = $categories;
        }
        return $this;
    }

    public function getCategories()
    {
        return $this->params['categories'];
    }

    public function getFirstCategory()
    {
        if (is_array($this->params['categories']) && count($this->params['categories']) > 0) {
            return $this->params['categories'][0];
        }

        return null;
    }

    public function setBrands($brands)
    {
        $brands = (array) $brands;
        if (count($brands) == 0) {
            $this->params['brands'] = null;
        } else {
            $this->params['brands'] = $brands;
        }

        return $this;
    }

    public function getBrands()
    {
        return $this->params['brands'];
    }

    public function getFirstBrand()
    {
        if (is_array($this->params['brands']) && count($this->params['brands']) > 0) {
            return $this->params['brands'][0];
        }
        return null;
    }

    public function setFilter($filter)
    {
        $this->params['filter'] = $filter;
        return $this;
    }

    public function getOffset()
    {
        return ($this->getPage() - 1) * $this->getLimit();
    }

    /**
     *
     * @return \Openstore\Core\Model\Browser\Filter\AbstractFilter
     */
    public function getFilter()
    {
        $filter_name = isset($this->params['filter']) ? $this->params['filter'] : "";
        if ($filter_name == '') {
            $filter_name = 'all';
        }
        return $this->getServiceLocator()->get('Openstore\Service')->getProductFilters()->getFilter($filter_name);
    }

    public function setPage($page)
    {
        $this->params['page'] = $page;
        return $this;
    }

    public function getPage()
    {
        return $this->params['page'];
    }

    public function setLimit($limit)
    {
        $this->params['limit'] = $limit;
        return $this;
    }

    public function getLimit()
    {
        return $this->params['limit'];
    }

    public function setSortBy($sortBy)
    {
        $this->params['sortBy'] = $sortBy;
        return $this;
    }

    public function getSortBy()
    {
        return $this->params['sortBy'];
    }

    public function setSortDir($sortDir)
    {
        $this->params['sortDir'] = $sortDir;
        return $this;
    }

    public function getSortDir()
    {
        return $this->params['sortDir'];
    }

    /**
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Openstore\Core\Model\Browser\Filter\AbstractFilter
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }
}
