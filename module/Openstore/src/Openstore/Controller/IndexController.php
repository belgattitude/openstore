<?php
namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;



class IndexController extends AbstractActionController
{
	/**
	 *
	 * @var Openstore\Service
	 */
	protected $service;
	
	public function onDispatch(\Zend\Mvc\MvcEvent $e) {
		parent::onDispatch($e);
	}
	
    public function indexAction()
    {
		
		$service = $this->getServiceLocator()->get('Openstore\Service');
		$view = new ViewModel();
		
		$userContext = $this->getServiceLocator()->get('Openstore\UserContext');

		/*
		$capabilities = $this->getServiceLocator()->get('Openstore\UserCapabilities');
//		echo '<pre>';
		var_dump($capabilities->getPricelists());
		var_dump($capabilities->getCustomers());
		
		*/
		/*
		echo '<pre>';
		var_dump($_SESSION);
		die();
		*/
		$view->test		= 'hello';
		
		/*
		echo '<pre>';
		var_dump(unserialize(file_get_contents('/tmp/aaaa.txt')));
		echo '</pre>';
		*/
        return $view;
    }

	
		
}
