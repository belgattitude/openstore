<?php

namespace OpenstoreApi;

use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Soluble\FlexStore\StoreInterface;
use Soluble\FlexStore\Writer\Zend\JsonWriter;
use Soluble\FlexStore\Writer\CSVWriter;
use Soluble\FlexStore\Writer\Excel\LibXLWriter;
use Soluble\FlexStore\Writer\SimpleXmlWriter;
use OpenstoreApi\Authorize\Exception\AuthorizationException;
use Soluble\Spreadsheet\Library\LibXL;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    public function init(ModuleManager $moduleManager)
    {
    }

    public function onBootstrap(MvcEvent $e)
    {

        /** @var \Zend\ModuleManager\ModuleManager $moduleManager */
        $moduleManager = $e->getApplication()->getServiceManager()->get('modulemanager');
        //$moduleManager->
        /** @var \Zend\EventManager\SharedEventManager $sharedEvents */
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        //$sharedEvents->attach('Zend\Mvc\Controller\AbstractRestfulController', MvcEvent::EVENT_DISPATCH, array($this, 'postProcess'), -100);
        $sharedEvents->attach(__NAMESPACE__, MvcEvent::EVENT_DISPATCH, [$this, 'postProcess'], -100);
        //$sharedEvents->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'errorProcess'), 999);
        $sharedEvents->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'errorProcess'], 999);

        //$eventManager = $moduleManager->getEventManager();
        //$eventManager        = $e->getApplication()->getEventManager();
        //$eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'postProcess'), -100);
        //$eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'errorProcess'), 999);
        /*
          $eventManager->attach('Zend\Mvc\Controller\AbstractRestfulController', MvcEvent::EVENT_DISPATCH, array($this, 'postProcess'), -100);
          $eventManager->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'errorProcess'), 999);
         */

        /*

         *
         */
    }

    /**
     * @param MvcEvent $e
     * @return null|\Zend\Http\PhpEnvironment\Response
     */
    public function postProcess(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if ($routeMatch) {
            $format = $routeMatch->getParam('format', false);
            if ($e->getResult() instanceof \Zend\View\Model\ViewModel) {
                if (is_array($e->getResult()->getVariables())) {
                    $vars = $e->getResult()->getVariables();
                } else {
                    $vars = null;
                }
            } else {
                $vars = $e->getResult();
            }


            switch ($format) {
                case 'json':
                    header("Access-Control-Allow-Origin: *");
                    if ($vars instanceof StoreInterface) {
                        $jsonWriter = new JsonWriter($vars);
                        $jsonWriter->setDebug($debug = false);
                        $jsonWriter->send();
                        die();
                    } else {
                        throw new \Exception('Response must include a valid StoreInterface object');
                    }
                    break;
                case 'xml':
                    if ($vars instanceof StoreInterface) {
                        $xmlWriter = new SimpleXmlWriter($vars);
                        $xmlWriter->send();

                        die();
                    } else {
                        throw new \Exception('Response must include a valid StoreInterface object');
                    }

                    break;
                case 'xlsx':
                    if ($vars instanceof StoreInterface) {
                        $lm = $e->getApplication()->getServiceManager()->get('LicenseManager');
                        $lic = $lm->get('libxl');

                        LibXL::setDefaultLicense(['name' => $lic['license_name'], 'key' => $lic['license_key']]);
                        //LibXLWriter::setDefaultLicense($lic['license_name'], $lic['license_key']);

                        $libxlWriter = new LibXLWriter($vars);
                        $libxlWriter->send();
                    } else {
                        throw new \Exception('Response must include a valid StoreInterface object');
                    }


                case 'csv':
                    if ($vars instanceof StoreInterface) {
                        if (array_key_exists('csv-enclosure', $_GET)) {
                            switch ($_GET['csv-enclosure']) {
                                case 'false':
                                case '':
                                    $enclosure = '';
                                    break;
                                default:
                                    $enclosure = $_GET['csv-enclosure'];
                            }
                        } else {
                            $enclosure = '"';
                        }


                        $options = [
                            'field_separator' => CSVWriter::SEPARATOR_TAB,
                            'line_separator' => CSVWriter::SEPARATOR_NEWLINE_UNIX,
                            'enclosure' => $enclosure,
                            'escape' => '"'
                        ];

                        $charset = trim($_GET['charset']);
                        if ($charset != '') {
                            $options['charset'] = $charset;
                        }

                        $csvWriter = new CSVWriter($vars);
                        $csvWriter->setOptions($options);
                        $csvWriter->send();
                    } else {
                        throw new \Exception('Response must include a valid StoreInterface object');
                    }


                default:
                    header("Access-Control-Allow-Origin: *");
                    throw new \Exception("Error '$format' format not supported");
            }
        }
    }

    /**
     * @param MvcEvent $e
     * @return null|\Zend\Http\PhpEnvironment\Response
     */
    public function errorProcess(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();

        //var_dump($e->getApplication()->get);die();
        if (php_sapi_name() != 'cli' && $routeMatch !== null && $routeMatch->getMatchedRouteName() == 'api/restful') {
            $format = $routeMatch->getParam('format', false);
            $eventParams = $e->getParams();

            /** @var array $configuration */
            $configuration = $e->getApplication()->getConfig();
            $error_message = "Something went wrong";

            $error_type = $eventParams['error'];

            $body = [
                'message' => $error_message,
                'success' => 0,
                'error' => [
                    'type' => $error_type,
                ]
            ];


            if (isset($eventParams['exception'])) {
                $reason_phrase = "Error: " . $eventParams['exception']->getMessage();

                /** @var \Exception $exception */
                $exception = $eventParams['exception'];

                if ($exception instanceof AuthorizationException) {
                    $error_type = "authorization-exception";
                    $body['error']['type'] = $error_type;
                    $body['message'] = "Authorization error, " . $exception->getMessage();
                }

                if ($configuration['errors']['show_exceptions']['message']) {
                    $body['error']['exception_message'] = $exception->getMessage();
                }
                if ($configuration['errors']['show_exceptions']['trace']) {
                    $body['error']['exception_trace'] = $exception->getTrace();
                }
            } else {
                $reason_phrase = "Error, something went wrong.";
            }



            switch ($format) {
                case 'json':
                    $e->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'application/json', true);
                    $e->getResponse()->setContent(json_encode($body));
                    break;

                case 'xml':
                    $e->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'application/xml', true);
                    $exception_message = $body['error']['exception_message'];
                    $error_type = $body['error']['type'];
                    $message = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                    $message .= "<response>\n\t<success>0</success>\n\t<message>$reason_phrase</message>";
                    $message .= "<error>\n\t<type>$error_type</type>";
                    $message .= "\t<exception_message>$exception_message</exception_message></error>\n</response>";
                    $e->getResponse()->setContent($message);
                    break;

                default:
                    $e->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'text/html', true);
                    $content = $reason_phrase;
                    $e->getResponse()->setContent($content);
                    break;
            }

            if ($eventParams['error'] === \Zend\Mvc\Application::ERROR_CONTROLLER_NOT_FOUND ||
                    $eventParams['error'] === \Zend\Mvc\Application::ERROR_ROUTER_NO_MATCH
            ) {
                $e->getResponse()->setStatusCode(\Zend\Http\PhpEnvironment\Response::STATUS_CODE_501);
            } else {
                $e->getResponse()->setStatusCode(\Zend\Http\PhpEnvironment\Response::STATUS_CODE_500);
                if ($reason_phrase != '') {
                    $e->getResponse()->setReasonPhrase($reason_phrase);
                }
            }

            $e->stopPropagation();
            return $e->getResponse();
        }
    }

    public function getConfig()
    {
        $config = array_merge(
            include __DIR__ . '/config/module.config.php',
            include __DIR__ . '/config/routes.config.php'
        );

        return $config;
    }

    public function getServiceConfig()
    {
        return [
            'initializers' => [
                'db' => function ($service, $sm) {
                    if ($service instanceof AdapterAwareInterface) {
                        $service->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
                    }
                },
                'sm' => function ($service, $sm) {
                    if ($service instanceof ServiceLocatorAwareInterface) {
                        $service->setServiceLocator($sm);
                    }
                }
            ],
            'factories' => [
                //'License\LicenceManager' => 'License\Service\LicenseManagerFactory',
                //'Test' => function ($sm) { return 'cool'; }
                'LicenseManager' => 'License\Service\LicenseManagerFactory'
            ],
            'aliases' => [
            ],
            'invokables' => [
                'Authorize\ApiKeyAccess' => 'OpenstoreApi\Authorize\ApiKeyAccess',
                'Api\ProductMediaService' => 'OpenstoreApi\Api\ProductMediaService',
                'Api\ProductCatalogService' => 'OpenstoreApi\Api\ProductCatalogService',
                'Api\ProductStockService' => 'OpenstoreApi\Api\ProductStockService',
                'Api\ProductBrandService' => 'OpenstoreApi\Api\ProductBrandService',
                'Api\NammProductCatalogService' => 'OpenstoreApi\Api\NammProductCatalogService',
            ]
        ];
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    'License' => __DIR__ . '/../Openstore/src/License'
                ],
            ],
        ];
    }
}
