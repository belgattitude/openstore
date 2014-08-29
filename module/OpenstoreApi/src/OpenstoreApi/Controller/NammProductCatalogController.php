<?php

namespace OpenstoreApi\Controller;

use OpenstoreApi\Mvc\Controller\AbstractRestfulController;
use OpenstoreApi\Authorize\ApiKeyAccess;
use Zend\View\Model\ViewModel;

class NammProductCatalogController extends AbstractRestfulController
{
	
	protected $collectionOptions = array('GET');
	//protected $resourceOptions = array('GET');
	protected $resourceOptions = array();
	
	
	/**
	 *
	 * @var \OpenstoreApi\Api\NammProductCatalogService
	 */
	protected $catalogService;
	
	/**
	 *
	 * @var ApiKeyAccess
	 */
	protected $apiKeyAccess;
        
        protected $template = 'namm_b2b/namm_item_v2011.1.phtml';
	
	
	public function onDispatch(\Zend\Mvc\MvcEvent $e) {
		$this->catalogService = $this->getServiceLocator()->get('Api\NammProductCatalogService');
		
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
                $view_path = realpath(dirname(__FILE__) . '/../../../view');
                $view_template = $view_path . DIRECTORY_SEPARATOR . $this->template;
                var_dump($view_template);
                var_dump(file_exists($view_template));
                //die();
                //$view_template = 'namm_item_v2011.1';
                //$view_template = 'test.phtml';

		$params = $this->params()->fromQuery();
		$this->apiKeyAccess->checkServiceAccess("2000-ProductCatalog");
		$this->apiKeyAccess->checkPricelistAccess($params['pricelist']);
		
		
		$api_key_log = $this->apiKeyAccess->addLog("2000-ProductCatalog");		
		$store = $this->catalogService->getList($params);
		if (array_key_exists('columns', $params)) {
			$columns = trim($params['columns']);
			if ($columns != '') {
				$store->getSource()->setColumns(explode(',', $columns));
			}
		}

                $renderer = new \Zend\View\Renderer\PhpRenderer();  
                $resolver = new \Zend\View\Resolver\TemplatePathStack(array(
                    'script_paths' => array(
                        $view_path)));
                $renderer->setResolver($resolver);                
                
                
                
                
                $view = new ViewModel(array(
                    'data' => $store->getSource()->getData(),
                ));
                
                $view->setTemplate($this->template);
                
                
                $a = htmlspecialchars( $renderer->render($view) );
                
                echo '<pre>' . $a . '</pre>';
                
            
                die('cool');                
                
                die ('cool');
		return $store;
	}



}
