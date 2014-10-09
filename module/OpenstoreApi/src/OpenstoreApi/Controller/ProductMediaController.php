<?php

namespace OpenstoreApi\Controller;

//use OpenstoreApi\Api\MediaService;
use OpenstoreApi\Mvc\Controller\AbstractRestfulController;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\JsonModel;
use Zend\Mvc\MvcEvent;

class ProductMediaController extends AbstractRestfulController {

    protected $collectionOptions = array('GET');
    //protected $resourceOptions = array('GET');
    protected $resourceOptions = array();

    /**
     *
     * @var \Openstore\Api\Api\ProductMediaService
     */
    protected $mediaService;

    public function onDispatch(\Zend\Mvc\MvcEvent $e) {
        $this->mediaService = $this->getServiceLocator()->get('Api\ProductMediaService');
        parent::onDispatch($e);
    }

    public function get($id) {
        die('hello');
        return $response;
    }

    public function getList() {

        $params = $this->params()->fromQuery();
        $store = $this->mediaService->getList($params);
        if (array_key_exists('columns', $params)) {
            $columns = str_replace(' ', '', $params['columns']);
            if ($columns != '') {
                //$store->getSource()->setColumns(explode(',', $columns));
                $limited_columns = explode(',', $columns);
                $cm = $store->getSource()->getColumnModel();
                $cm->includeOnly($limited_columns);
            }
        }
        
        return $store;

        //return new JsonModel($data);
    }

}
