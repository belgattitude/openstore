<?php

namespace OpenstoreApi\Controller;

use OpenstoreApi\Mvc\Controller\AbstractRestfulController;
use OpenstoreApi\Authorize\ApiKeyAccess;

class ProductStockController extends AbstractRestfulController
{
    protected $collectionOptions = ['GET'];
    //protected $resourceOptions = array('GET');
    protected $resourceOptions = [];

    /**
     *
     * @var \OpenstoreApi\Api\ProductStockService
     */
    protected $stockService;

    /**
     *
     * @var ApiKeyAccess
     */
    protected $apiKeyAccess;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->stockService = $this->getServiceLocator()->get('Api\ProductStockService');

        $api_key = $this->params()->fromQuery('api_key');
        $this->apiKeyAccess = new ApiKeyAccess($api_key, $this->getServiceLocator());
        parent::onDispatch($e);
    }

    public function get($id)
    {
        die('hello');
        return $response;
    }

    public function getList()
    {
        $params = $this->params()->fromQuery();
        $this->apiKeyAccess->checkServiceAccess("2000-ProductCatalog");
        $this->apiKeyAccess->checkPricelistAccess($params['pricelist']);


        $api_key_log = $this->apiKeyAccess->addLog("2000-ProductCatalog");
        $store = $this->stockService->getList($params);

        if (array_key_exists('columns', $params)) {
            $columns = str_replace(' ', '', $params['columns']);
            if ($columns != '') {
                //$store->getSource()->setColumns(explode(',', $columns));
                $limited_columns = explode(',', $columns);
                $cm = $store->getSource()->getColumnModel();
                $cm->includeOnly($limited_columns);
            }
        }

        return $store;
    }
}
