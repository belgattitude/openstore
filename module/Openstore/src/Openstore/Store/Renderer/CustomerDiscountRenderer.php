<?php

namespace Openstore\Store\Renderer;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Soluble\FlexStore\Renderer\RowRendererInterface;
use Openstore\Model\DiscountCondition;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
//use Zend\Db\Sql\Expression;
use ArrayObject;

class CustomerDiscountRenderer implements RowRendererInterface, ServiceLocatorAwareInterface, AdapterAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     *
     * @var Adapter
     */
    protected $adapter;
    

    /**
     *
     * @var integer
     */
    protected $customer_id;
    
    /**
     *
     * @var string
     */
    protected $pricelist;
    
    /**
     *
     * @var array
     */
    protected $columns = array(
        'source_price'      => 'price',
        'source_list_price' => 'list_price',
        
        'source_type_id'    => 'type_id',
        
        'source_discount_1' => 'discount_1',
        'source_discount_2' => 'discount_2',
        'source_discount_3' => 'discount_3',
        'source_discount_4' => 'discount_4',
        

        // All those columns must be available in row
        'source_product_id'      => 'product_id',
        'source_brand_id'         => 'brand_id',
        'source_category_id'    => 'category_id',
        'source_product_group_id'   => 'group_id',
        
        'source_status_reference'   => 'status_reference',
        
        'target_discount_1' => 'my_discount_1',
        'target_discount_2' => 'my_discount_2',
        'target_discount_3' => 'my_discount_3',
        'target_discount_4' => 'my_discount_4',
        
        'target_price'      => 'my_price',
        
        
    );
    
    /**
     *
     * @var ArrayObject|null
     */
    protected $exclusions;
    
    /**
     *
     * @var ArrayObject|null
     */
    protected $loaded_discounts;

    
    protected function loadExclusions()
    {
        if ($this->exclusions === null) {
            $this->exclusions = new ArrayObject();
            $this->exclusions['product_type'] = array();
            $select = new Select();
            $select->from('product_type', array())
                   ->where->equalTo('flag_enable_discount_condition', 0);
            $select->columns(array('type_id'), true);
                    
            $sql = new Sql($this->adapter);
            $sql_string = $sql->getSqlStringForSqlObject($select);

            $results = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);
            foreach ($results as $result) {
                $this->exclusions['product_type'][] = $result['type_id'];
            }
        }
    }
  
    
    /**
     *
     * @param integer $customer_id
     * @param string $pricelist pricelist reference
     */
    public function setParams($customer_id, $pricelist)
    {
        $this->customer_id = $customer_id;
        $this->pricelist = $pricelist;
    }
    
    

    /**
     *
     */
    protected function loadCustomerDiscounts()
    {
        $sl = $this->getServiceLocator();
        $dc = $sl->get('Model\DiscountCondition');
        $matrix = $dc->getCustomerMatrix($this->customer_id, $this->pricelist);
        $this->loaded_discounts = $matrix;
        $this->loadExclusions();
    }

    /**
     *
     * @throws \Exception
     * @param ArrayObject $row
     */
    public function apply(ArrayObject $row)
    {
        if (!$this->loaded_discounts) {
            $this->loadCustomerDiscounts();
        }
        
        $list_price = $row[$this->columns['source_list_price']];
        $price      = $row[$this->columns['source_price']];
        
        $discount_1 =  $row[$this->columns['source_discount_1']];
        $discount_2 =  $row[$this->columns['source_discount_2']];
        $discount_3 =  $row[$this->columns['source_discount_3']];
        $discount_4 =  $row[$this->columns['source_discount_4']];
        
        $type_id    = $row[$this->columns['source_type_id']];
        
        $sd = null;
        
        if (!in_array($type_id, $this->exclusions['product_type'])
                && $this->loaded_discounts->count() > 0) {
            $ld = $this->loaded_discounts;
            
            $product_id         = $row[$this->columns['source_product_id']];
            $brand_id           = $row[$this->columns['source_brand_id']];
            $product_group_id   = $row[$this->columns['source_product_group_id']];
            $category_id        = $row[$this->columns['source_category_id']];
            
            // TEST EXISTENCE in order PRODUCT/...
            if (isset($ld[DiscountCondition::TYPE_PRODUCT][$product_id])) {
                $sd = $ld[DiscountCondition::TYPE_PRODUCT][$product_id];
            } elseif (isset($ld[DiscountCondition::TYPE_BRAND_PRODUCT_GROUP][$brand_id][$product_group_id])) {
                $sd = $ld[DiscountCondition::TYPE_BRAND_PRODUCT_GROUP][$brand_id][$product_group_id];
            } elseif (isset($ld[DiscountCondition::TYPE_PRODUCT_GROUP][$product_group_id])) {
                $sd = $ld[DiscountCondition::TYPE_PRODUCT_GROUP][$product_group_id];
            } elseif (isset($ld[DiscountCondition::TYPE_BRAND][$brand_id])) {
                $sd = $ld[DiscountCondition::TYPE_BRAND][$brand_id];
            } elseif (isset($ld[DiscountCondition::TYPE_CATEGORY][$category_id])) {
                $sd = $ld[DiscountCondition::TYPE_CATEGORY][$category_id];
            } elseif (isset($ld[DiscountCondition::TYPE_CUSTOMER][$this->customer_id])) {
                $sd = $ld[DiscountCondition::TYPE_CUSTOMER][$this->customer_id];
            }
        }

        // Special discount found

        if ($sd === null) {
            $row[$this->columns['target_price']] = $price;
            $row[$this->columns['target_discount_1']] = $discount_1;
            $row[$this->columns['target_discount_2']] = $discount_2;
            $row[$this->columns['target_discount_3']] = $discount_3;
            $row[$this->columns['target_discount_4']] = $discount_4;
        } else {
            $status = $row[$this->columns['source_status_reference']];

            // Remove discount 2 (rep discount) when liquidation or surstock
            if (in_array($status, array('L', 'S'))) {
                $sd['discount_2'] = 0.0;
            }
            
            // Remove discount 1 (company discount) for all export pricelists
            if (in_array($this->pricelist, array('100B', '120B', '100U', '120U'))) {
                $sd['discount_1'] = 0.0;
            }
            
            // Calculate new price from list_price

            $d1 = max($sd['discount_1'], $discount_1);
            $d2 = max($sd['discount_2'], $discount_2);
            $d3 = max($sd['discount_3'], $discount_3);
            $d4 = max($sd['discount_4'], $discount_4);
            
            $target_price = $list_price * (1-($d1/100))
                                        * (1-($d2/100))
                                        * (1-($d3/100))
                                        * (1-($d4/100));

            $row[$this->columns['target_discount_1']] = $d1;
            $row[$this->columns['target_discount_2']] = $d2;
            $row[$this->columns['target_discount_3']] = $d3;
            $row[$this->columns['target_discount_4']] = $d4;
            
            $row[$this->columns['target_price']] = $target_price;
        }
    }


    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return CustomerDiscountRenderer
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Set db adapter
     *
     * @param Adapter $adapter
     * @return CustomerDiscountRenderer
     */
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     *
     * @return Adapter
     */
    public function getDbAdapter()
    {
        return $this->adapter;
    }

    /**
     * Return the list of columns required in order to use this renderer
     * @return array
     */
    public function getRequiredColumns()
    {
        return array_values($this->columns);
    }
}
