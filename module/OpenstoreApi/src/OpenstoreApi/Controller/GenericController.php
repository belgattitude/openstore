<?php

namespace OpenstoreApi\Controller;

use OpenstoreApi\Mvc\Controller\AbstractRestfulController;
use OpenstoreApi\Authorize\ApiKeyAccess;
use Zend\View\Model\ViewModel;

class GenericController extends AbstractRestfulController
{
    protected $collectionOptions = ['GET'];
    //protected $resourceOptions = array('GET');
    protected $resourceOptions = [];

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

        // http://localhost/workspace/openstore/public/api/generic?template=namm_item_v2007.1&pricelist=BE&limit=10&language=en&limit=10&api_key=TEST123-TEST456-TEST789

        $params = $this->params()->fromQuery();
        $this->apiKeyAccess->checkPricelistAccess($params['pricelist']);


        $api_key_log = $this->apiKeyAccess->addLog("2000-ProductCatalog");
        $store = $this->catalogService->getList($params);

        if (isset($params['limit']) && is_numeric($params['limit'])) {
            $limit = $params['limit'];
            $store->getOptions()->setLimit($limit);
        } else {
            $limit = 0;
        }

        $view_renderer = $this->getViewRenderer();


        $view = new ViewModel([
            //'data' => $store->getSource()->getData(),
            'store' => $store,
        ]);

        $view->setTemplate($view_template);

        try {
            $output = $view_renderer->render($view);
            if (array_key_exists('validate', $params) && $params['validate'] == 'true') {
                $xsd_file = $this->view_directory . DIRECTORY_SEPARATOR . $this->template['validate']['list'];

                if (!file_exists($xsd_file)) {
                    throw new \Exception("Schema file does not exists:" . $xsd_file);
                }

                $this->validateXml($output, realpath($xsd_file));
            }
            header('Content-Type: text/xml');
            if ($this->template['filename']['list'] != '') {
                $filename = $this->template['filename']['list'];
                if ($limit == 0 || $limit > 200) {
                    header('Content-Disposition: attachement; filename="' . $filename . '"');
                }
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

        if (!file_exists($xsd_file)) {
            throw new \Exception("Cannot locate xsd file '$xsd_file'");
        }
        if (!$xml->schemaValidate($xsd_file)) {
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
        $resolver = new \Zend\View\Resolver\TemplatePathStack([
            'script_paths' => [
                $this->view_directory]]);
        $renderer->setResolver($resolver);
        return $renderer;
    }



    /**
     * Method to refactor in a configuration file later
     * @return array
     */
    protected function getRegisteredTemplates()
    {
        $templates = [
            'namm_item_v2007.1' => [
                'view' => [
                    'list' => 'namm_b2b/namm_item_v2007.1.phtml'
                ],
                'validate' => [
                    'list' => 'namm_b2b/xsd/item_v2007.1.xsd'
                ],
                'filename' => [
                    'list' => 'namm_item_v2007.1.xml'
                ],
                'check_service_access' => [
                    '2000-ProductCatalog'
                ]
            ],
            'namm_item_v2015.1' => [
                'view' => [
                    'list' => 'namm_b2b/namm_item_v2015.1.phtml'
                ],
                'validate' => [
                    'list' => 'namm_b2b/xsd/item_v2015.1.xsd'
                ],
                'filename' => [
                    'list' => 'namm_item_v2015.1.xml'
                ],
                'check_service_access' => [
                    '2000-ProductCatalog'
                ]
            ]
        ];
        return $templates;
    }
}
