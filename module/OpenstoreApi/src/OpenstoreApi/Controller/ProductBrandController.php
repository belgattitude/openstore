<?php

namespace OpenstoreApi\Controller;

//use OpenstoreApi\Api\MediaService;
use OpenstoreApi\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class ProductBrandController extends AbstractRestfulController
{
    protected $collectionOptions = ['GET'];
    //protected $resourceOptions = array('GET');
    protected $resourceOptions = [];

    /**
     *
     * @var \Openstore\Api\Api\ProductBrandService
     */
    protected $brandService;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->brandService = $this->getServiceLocator()->get('Api\ProductBrandService');
        parent::onDispatch($e);
    }

    public function get($id)
    {
        die('hello');
        return $response;
    }

    public function getList()
    {
        $params = $this->params()->fromQuery();
        $store = $this->brandService->getList($params);
        return $store;

        //return new JsonModel($data);
    }
}
