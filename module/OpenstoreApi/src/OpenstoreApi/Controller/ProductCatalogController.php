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
		$this->apiKeyAccess   = $this->getServiceLocator()->get('Authorize\ApiKeyAccess');
		parent::onDispatch($e);
	}	
	
	
	public function get($id) {
		die('hello');
		return $response;
	}

	public function getList() 
	{
		//var_dump(get_class($this->apiKeyAccess));
		//die();
		$params = $this->params()->fromQuery();
		$store = $this->catalogService->getList($params);
		return $store;
	}



}
