<?php

namespace OpenstoreApi\Controller;

use OpenstoreApi\Mvc\Controller\AbstractRestfulController;
use Zend\Mvc\MvcEvent;

class ProductVideoController extends AbstractRestfulController
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
     * @var \OpenstoreApi\Api\ProductVideoService
     */
    protected $videoService;

    public function onDispatch(MvcEvent $e)
    {
        $this->videoService = $this->getServiceLocator()->get('Api\ProductVideoService');
        parent::onDispatch($e);
    }


    public function getList()
    {
        $params = $this->params()->fromQuery();
        $store = $this->videoService->getList($params);
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
