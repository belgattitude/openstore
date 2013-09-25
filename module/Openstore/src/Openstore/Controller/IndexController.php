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
		$capabilities = $this->getServiceLocator()->get('Openstore\UserCapabilities');
//		echo '<pre>';
		var_dump($capabilities->getPricelists());
		var_dump($capabilities->getCustomers());
		
		
		
		$view->test		= 'hello';
		
		
        return $view;
    }

	
		
}
