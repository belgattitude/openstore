<?php

namespace OpenstoreApi\Controller;

use OpenstoreApi\Mvc\Controller\AbstractRestfulController;
use OpenstoreApi\Authorize\ApiKeyAccess;
use Zend\View\Model\ViewModel;
use Soluble\FlexStore\Options;

class GenericController extends AbstractRestfulController
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


    protected function loadTemplate()
    {
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

    public function get($id)
    {
        die('hello');
        return $response;
    }


    public function getList()
    {
        $view_template = $this->template['view']['list'];


        $params = $this->params()->fromQuery();

        $this->apiKeyAccess->checkPricelistAccess($params['pricelist']);


        $api_key_log = $this->apiKeyAccess->addLog("2000-ProductCatalog");
        $store = $this->catalogService->getList($params);


        $view_renderer = $this->getViewRenderer();


        $view = new ViewModel(array(
            //'data' => $store->getSource()->getData(),
            'store' => $store,
        ));

        $view->setTemplate($view_template);

        try {
            $output = $view_renderer->render($view);
            if ($params['validate'] == 'true') {
                $this->validateXml($output, $this->template['validate']['list']);
            }
            header('Content-Type: text/xml');
            if ($this->template['filename']['list'] != '') {
                $filename = $this->template['filename']['list'];
                header('Content-Disposition: attachement; filename="' . $filename . '"');
            }
            echo $output;
        } catch (\Exception $e) {
            throw $e;
        }
        die();
    }

    protected function validateXml($xml_string, $xsd_file)
    {

        // Enable user error handling
        libxml_use_internal_errors(true);

        $xml = new \DOMDocument();
        $xml->loadXML($xml_string);

        if (!$xml->schemaValidate($this->view_directory . DIRECTORY_SEPARATOR . $xsd_file)) {
            print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                $return = "<br/>\n";
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $return .= "<b>Warning $error->code</b>: ";
                        break;
                    case LIBXML_ERR_ERROR:
                        $return .= "<b>Error $error->code</b>: ";
                        break;
                    case LIBXML_ERR_FATAL:
                        $return .= "<b>Fatal Error $error->code</b>: ";
                        break;
                }
                $return .= trim($error->message);
                if ($error->file) {
                    $return .=    " in <b>$error->file</b>";
                }
                $return .= " on line <b>$error->line</b>\n";
                echo $return;
            }
            libxml_clear_errors();
            die();
        }
    }

    /**
     *
     * @return \Zend\View\Renderer\PhpRenderer
     */
    protected function getViewRenderer()
    {
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
                'validate' => array(
                    'list' => 'namm_b2b/xsd/item_v2007.1.xsd'
                ),
                'filename' => array(
                    'list' => 'namm_item_v2007.1.xml'
                ),
                'check_service_access' => array(
                    '2000-ProductCatalog'
                )
            ),
            'namm_item_v2011.1' => array(
                'view' => array(
                    'list' => 'namm_b2b/namm_item_v2011.1.phtml'
                ),
                'validate' => array(
                    'list' => 'namm_b2b/xsd/item_v2011.1.xsd'
                ),
                'filename' => array(
                    'list' => 'namm_item_v2011.1.xml'
                ),
                'check_service_access' => array(
                    '2000-ProductCatalog'
                )
            )
        );
        return $templates;
    }
}
