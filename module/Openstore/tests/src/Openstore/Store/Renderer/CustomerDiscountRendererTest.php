<?php

namespace Openstore\Store\Renderer;

use ModulesTests\ServiceManagerGrabber;
use Zend\ServiceManager\ServiceManager;
use Soluble\FlexStore\Store;
use Soluble\FlexStore\Column\Column;
use Soluble\FlexStore\Column\ColumnType;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-10-14 at 16:06:43.
 */
class CustomerDiscountRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerDiscountRenderer
     */
    protected $cdr;

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
        $serviceManagerGrabber = new ServiceManagerGrabber();
        $this->serviceManager = $serviceManagerGrabber->getServiceManager();
        $this->cdr = $this->serviceManager->get('Store\Renderer\CustomerDiscount');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     *
     * @param string $pricelist_reference
     * @param int $customer_id
     * @param string $language
     * @param int $limit
     * @return Store
     */
    protected function getCatalogStore($pricelist_reference, $customer_id, $language = 'en', $limit = 1000)
    {
        $product = $this->serviceManager->get('Model\Product');

        $browser = $product->getBrowser()->setSearchParams(
            [
                            'language' => $language,
                            'pricelist' => $pricelist_reference,
                        ]
        )
                        ->setLimit($limit);

                        return $browser->getStore();
    }


    public function testRendering()
    {
        $customer_id = 3521;
        $pricelist   = 'FR';

        $cdr = $this->serviceManager->get('Store\Renderer\CustomerDiscount');
        $cdr->setParams($customer_id, $pricelist);
        $store = $this->getCatalogStore($pricelist, $customer_id, 'en', 30);

        $store->getSource()->getSelect()->where->in('p.product_id', array(17436, 16978));
        var_dump($store->getSource()->__toString());
        die();

        $cm = $store->getColumnModel();

        $cm->add(new Column('my_price', array('type' => ColumnType::TYPE_DECIMAL)));
        $cm->add(new Column('my_discount_1', array('type' => ColumnType::TYPE_DECIMAL)));
        $cm->add(new Column('my_discount_2', array('type' => ColumnType::TYPE_DECIMAL)));
        $cm->add(new Column('my_discount_3', array('type' => ColumnType::TYPE_DECIMAL)));
        $cm->add(new Column('my_discount_4', array('type' => ColumnType::TYPE_DECIMAL)));

        $cm->addRowRenderer($cdr);

        $data = $store->getData()->toArray();
        foreach ($data as $row) {
            $arr = array(
               'product_id' => $row['product_id'],
               'list_price' => $row['list_price'],
               'discount_1' => $row['discount_1'],
               'discount_2' => $row['discount_2'],
               'price'      => $row['price'],
               'my_discount_1' => $row['my_discount_1'],
               'my_discount_2' => $row['my_discount_2'],
               'my_price'      => $row['my_price'],
            );
            echo join("\t", $arr);
            echo "\n";
        }


        die();
    }

    /**
     * @covers Openstore\Store\Renderer\CustomDiscountRenderer::setParams
     * @todo   Implement testSetParams().
     */
    public function testSetParams()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Openstore\Store\Renderer\CustomDiscountRenderer::apply
     * @todo   Implement testApply().
     */
    public function testApply()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Openstore\Store\Renderer\CustomDiscountRenderer::setServiceLocator
     * @todo   Implement testSetServiceLocator().
     */
    public function testSetServiceLocator()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Openstore\Store\Renderer\CustomDiscountRenderer::getServiceLocator
     * @todo   Implement testGetServiceLocator().
     */
    public function testGetServiceLocator()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Openstore\Store\Renderer\CustomDiscountRenderer::setDbAdapter
     * @todo   Implement testSetDbAdapter().
     */
    public function testSetDbAdapter()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Openstore\Store\Renderer\CustomDiscountRenderer::getDbAdapter
     * @todo   Implement testGetDbAdapter().
     */
    public function testGetDbAdapter()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
