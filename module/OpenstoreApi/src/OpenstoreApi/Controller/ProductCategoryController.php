<?php

namespace OpenstoreApi\Controller;

//use OpenstoreApi\Api\MediaService;
use OpenstoreApi\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class ProductCategoryController extends AbstractRestfulController
{
    protected $collectionOptions = ['GET'];
    //protected $resourceOptions = array('GET');
    protected $resourceOptions = [];

    /**
     *
     * @var \OpenstoreApi\Api\ProductCategoryService
     */
    protected $categService;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->categService = $this->getServiceLocator()->get('Api\ProductCategoryService');
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
        $store = $this->categService->getList($params);
        return $store;

        //return new JsonModel($data);
    }
}
