<?php
namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
	/**
	 *
	 * @var \Openstore\Service
	 */
	protected $service;
	
	public function onDispatch(\Zend\Mvc\MvcEvent $e) {
		parent::onDispatch($e);
	}
	
    public function indexAction()
    {
		$service = $this->getServiceLocator()->get('Openstore\Service');
		$view = new ViewModel();
		
		$userContext = $service->getUserContext();
		
		// get the associated customer_id and the pricelist_id
		
		var_dump($userContext->getCustomerId());
		
		
		$view->test		= 'hello';
		
		
        return $view;
    }

	
		
}
