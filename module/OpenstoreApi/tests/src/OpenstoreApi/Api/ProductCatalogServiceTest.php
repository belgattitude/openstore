<?php

namespace OpenstoreApi\Api;

use ModulesTests\ServiceManagerGrabber;
use Zend\ServiceManager\ServiceManager;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-10-20 at 12:01:52.
 */
class ProductCatalogServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductCatalogService
     */
    protected $catalogService;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $serviceManagerGrabber   = new ServiceManagerGrabber();
        $this->serviceManager = $serviceManagerGrabber->getServiceManager();
        $this->catalogService = $this->serviceManager->get('Api\ProductCatalogService');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testGetList()
    {
        $params = [
          'pricelist' => 'FR',
          'language' => 'fr',
          //'brands' => 'STAG,REMO',
          //'groups' => array('P0')  ,
          //'limit' => 100,
          'columns' => 'product_id,product_reference,picture_url,my_discount_1,my_discount_2,my_discount_3,my_price'
          //'offset' => null

        ];

        $store = $this->catalogService->getList($params);
        $product_id = 14349;
        $select = $store->getSource()->getSelect();
        $select->where(['p.product_id' => $product_id]);
        //$sql_string = $store->getSource()->__toString();

        if (array_key_exists('columns', $params)) {
            $columns = str_replace(' ', '', $params['columns']);
            if ($columns != '') {
                //$store->getSource()->setColumns(explode(',', $columns));
                $limited_columns = explode(',', $columns);
                $cm = $store->getSource()->getColumnModel();
                $cm->includeOnly($limited_columns);
            }
        }


        $row = $store->getData()->current();
        $this->assertEquals($product_id, $row['product_id']);
        $this->assertContains('€', $row['my_price']);
        $this->assertContains('%', $row['my_discount_1']);
        $this->assertEquals($limited_columns, array_keys((array) $row));
    }

    public function testGetListWithoutColumns()
    {
        $params = [
          'pricelist' => 'FR',
          'language' => 'fr',
          //'limit' => 100,

        ];
        $store = $this->catalogService->getList($params);
        $product_id = 14349;
        $select = $store->getSource()->getSelect();
        $select->where(['p.product_id' => $product_id]);
        $sql_string = $store->getSource()->__toString();
        $row = $store->getData()->current();
        $this->assertEquals($product_id, $row['product_id']);

        var_dump((array) $row);
        die();
        //$columns = array_keys((array) $row);
        //var_dump($columns); die('cool');
    }
}
