<?php

namespace OpenstoreApi\Controller;

use OpenstoreApi\Mvc\Controller\AbstractRestfulController;
use OpenstoreApi\Authorize\ApiKeyAccess;
use Zend\View\Model\ViewModel;

class GenericController extends AbstractRestfulController {

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
    
    
    /**
     *
     * @var array
     */
    protected $templates;
    
    /**
     *
     * @var array
     */
    protected $template;
    
    /**
     *
     * @var string
     */
    protected $view_directory;     

    public function onDispatch(\Zend\Mvc\MvcEvent $e) 
    {
        $this->catalogService = $this->getServiceLocator()->get('Api\NammProductCatalogService');

        $api_key = $this->params()->fromQuery('api_key');
        $this->apiKeyAccess = new ApiKeyAccess($api_key, $this->getServiceLocator());
        
        $this->templates = $this->getRegisteredTemplates();
        $this->view_directory = realpath(dirname(__FILE__) . '/../../../view');
        
        $this->loadTemplate();
        
        
        parent::onDispatch($e);
    }

    
    protected function loadTemplate() {
        $template = $this->params()->fromQuery('template');
        if (!array_key_exists($template, $this->templates)) {
            throw new \Exception("Template '$template' does not exists");
        }
        $this->template = $this->templates[$template];
        
        // Check access 
        foreach ((array) $this->template['check_service_access'] as $sa) {
            $this->apiKeyAccess->checkServiceAccess($sa);
        }
    }
    
    public function get($id) {
        die('hello');
        return $response;
    }
    

    public function getList() {
        
        
        $view_template = $this->template['view']['list'];


        $params = $this->params()->fromQuery();
        
        $this->apiKeyAccess->checkPricelistAccess($params['pricelist']);


        $api_key_log = $this->apiKeyAccess->addLog("2000-ProductCatalog");
        $store = $this->catalogService->getList($params);
 

        $view_renderer = $this->getViewRenderer();


        $view = new ViewModel(array(
            'data' => $store->getSource()->getData(),
        ));

        $view->setTemplate($view_template);

        header('Content-Type: text/xml');
        $output = $view_renderer->render($view);

        echo $output;
        die();
    }

    /**
     * 
     * @return \Zend\View\Renderer\PhpRenderer
     */
    protected function getViewRenderer() {
        $renderer = new \Zend\View\Renderer\PhpRenderer();
        $resolver = new \Zend\View\Resolver\TemplatePathStack(array(
            'script_paths' => array(
                $this->view_directory)));
        $renderer->setResolver($resolver);
        return $renderer;

    }
           
    
    
    /**
     * Method to refactor in a configuration file later
     * @return array
     */
    protected function getRegisteredTemplates()
    {
        $templates = array(
            'namm_item_v2007.1' => array(
                'view' => array(
                    'list' => 'namm_b2b/namm_item_v2007.1.phtml'
                ),
                'check_service_access' => array(
                    '2000-ProductCatalog'
                )
            ),            
            'namm_item_v2011.1' => array(
                'view' => array(
                    'list' => 'namm_b2b/namm_item_v2011.1.phtml'
                ),
                'check_service_access' => array(
                    '2000-ProductCatalog'
                )
            )
        );
        return $templates;
    }        
    

}
