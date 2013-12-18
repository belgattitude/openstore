<?php

namespace OpenstoreApi\Controller;

use OpenstoreApi\Mvc\Controller\AbstractRestfulController;
use OpenstoreApi\Authorize\ApiKeyAccess;

class ProductCatalogController extends AbstractRestfulController
{
	
	protected $collectionOptions = array('GET');
	//protected $resourceOptions = array('GET');
	protected $resourceOptions = array();
	
	
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
	
	
	public function onDispatch(\Zend\Mvc\MvcEvent $e) {
		$this->catalogService = $this->getServiceLocator()->get('Api\ProductCatalogService');
		
		$api_key = $this->params()->fromQuery('api_key');
		$this->apiKeyAccess = new ApiKeyAccess($api_key, $this->getServiceLocator());
		parent::onDispatch($e);
	}	
	
	
	public function get($id) {
		die('hello');
		return $response;
	}

	public function getList() 
	{

		$this->apiKeyAccess->checkServiceAccess("2000-ProductCatalog");
		
//var_dump($this->getEvent()->getApplication()); die();		
		
		//$this->apiKeyAccess->c
		$params = $this->params()->fromQuery();
		$store = $this->catalogService->getList($params);
		return $store;
	}



}
