<?php

namespace OpenstoreApi\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\JsonModel;

class MediaController extends AbstractRestfulController
{

	protected $collectionOptions = array('GET');
	protected $resourceOptions = array('GET');

	public function get($id) {
		$response = $this->getResponseWithHeader()
				->setContent(__METHOD__ . ' get current data with id =  ' . $id);
		return $response;
	}

	public function getList() {
		$data = array(
			'phone' => '+30123456789',
			'email' => 'email@domain',
		);

		return $data;
	}

	// configure response
	public function getResponseWithHeader() {
		$response = $this->getResponse();
		$response->getHeaders()
				//make can accessed by *  
				->addHeaderLine('Access-Control-Allow-Origin', '*')
				//set allow methods
				->addHeaderLine('Access-Control-Allow-Methods', 'POST PUT DELETE GET');

		return $response;
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

	public function setEventManager(EventManagerInterface $events) {
		// events property defined in AbstractController
		$this->events = $events;

		// Register the listener and callback method with a priority of 10
		$events->attach('dispatch', array($this, 'checkOptions'), 10);
	}

	public function checkOptions($e) {
		if (in_array($e->getRequest()->getMethod(), $this->_getOptions())) {
			// method allowed, nothing to do
			return;
		}
		// Method not allowed
		$response = $this->getResponse();
		$response->setStatusCode(405);
		return $response;
	}

}
