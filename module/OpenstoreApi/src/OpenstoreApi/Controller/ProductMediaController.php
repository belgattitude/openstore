<?php

namespace OpenstoreApi\Controller;

use OpenstoreApi\Mvc\Controller\AbstractRestfulController;
use Zend\Mvc\MvcEvent;

class ProductMediaController extends AbstractRestfulController
{
    /**
     * @var array
     */
    protected $collectionOptions = ['GET'];

    /**
     * @var array
     */
    protected $resourceOptions = [];

    /**
     *
     * @param MvcEvent $event
     * @var \OpenstoreApi\Api\ProductMediaService
     */
    protected $mediaService;

    public function onDispatch(MvcEvent $e)
    {
        $this->mediaService = $this->getServiceLocator()->get('Api\ProductMediaService');
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
    }
}
