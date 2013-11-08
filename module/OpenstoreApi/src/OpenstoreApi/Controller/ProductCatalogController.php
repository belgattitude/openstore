<?php

namespace OpenstoreApi\Controller;

//use OpenstoreApi\Api\MediaService;
use OpenstoreApi\Mvc\Controller\AbstractRestfulController;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\JsonModel;
use Zend\Mvc\MvcEvent;

class ProductCatalogController extends AbstractRestfulController
{
	
	protected $collectionOptions = array('GET');
	//protected $resourceOptions = array('GET');
	protected $resourceOptions = array();
	
	
	/**
	 *
	 * @var \Openstore\Api\Api\ProductCatalogService
	 */
	protected $catalogService;
	
	
	public function onDispatch(\Zend\Mvc\MvcEvent $e) {
		$this->catalogService = $this->getServiceLocator()->get('Api\ProductCatalogService');
		parent::onDispatch($e);
	}	
	
	
	public function get($id) {
		die('hello');
		return $response;
	}

	public function getList() {
		
		$params = $this->params()->fromQuery();
		$store = $this->catalogService->getList($params);
		return $store;

		//return new JsonModel($data);
	}


	protected function _getOptions() {
		if ($this->params()->fromRoute('id', false)) {
			// we have an id, return specific item
			return $this->resourceOptions;
		}
		// no ID, return collection
		
		return $this->collectionOptions;
	}

	public function options() {
		$response = $this->getResponse();
		$response->getHeaders()
				 ->addHeaderLine('Allow', implode(',', $this->_getOptions()));

		return $response;
	}
/*
 * TODO find a way to make it work
	public function setEventManager(EventManagerInterface $events) {
		// events property defined in AbstractController
		$this->events = $events;

		// Register the listener and callback method with a priority of 10
		$events->attach('dispatch', array($this, 'checkOptions'), 10);
	}
*/
	public function checkOptions(MvcEvent $e) {
		if (in_array($e->getRequest()->getMethod(), $this->_getOptions())) {
			// method allowed, nothing to do
			return $e->getResponse();
		}
		// Method not allowed
		$response = $this->getResponse();
		$response->setStatusCode(405);
		return $response;
	}

}
