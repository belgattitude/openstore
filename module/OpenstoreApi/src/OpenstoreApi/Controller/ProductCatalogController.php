<?php

namespace OpenstoreApi\Controller;

use OpenstoreApi\Mvc\Controller\AbstractRestfulController;
use OpenstoreApi\Authorize\ApiKeyAccess;

class ProductCatalogController extends AbstractRestfulController
{
    protected $collectionOptions = ['GET'];
    //protected $resourceOptions = array('GET');
    protected $resourceOptions = [];

    /**
     *
     * @var \OpenstoreApi\Api\ProductCatalogService
     */
    protected $catalogService;

    /**
     *
     * @var ApiKeyAccess
     */
    protected $apiKeyAccess;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->catalogService = $this->getServiceLocator()->get('Api\ProductCatalogService');

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

        // Find customer
        $customers = $this->apiKeyAccess->getCustomers();
        if (count($customers) > 1) {
            throw new \Exception("API key is linked to multiple customers, not yet supported. Contact us.");
        }

        $customer_id = $customers[0];
        $params['customer_id'] = $customer_id;


        $api_key_log = $this->apiKeyAccess->addLog("2000-ProductCatalog");
        $store = $this->catalogService->getList($params);
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
