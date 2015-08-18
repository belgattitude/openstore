<?php

namespace Openstore\Model;

use Openstore\Core\Model\AbstractModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Soluble\FlexStore\Store;
use Soluble\FlexStore\Source\Zend\SqlSource;
use ArrayObject;

class DiscountCondition implements AdapterAwareInterface
{
    const TYPE_PRODUCT              = 'product';
    const TYPE_BRAND                = 'brand';
    const TYPE_PRODUCT_GROUP        = 'group';
    const TYPE_BRAND_PRODUCT_GROUP  = 'brand/product_group';
    const TYPE_CUSTOMER             = 'customer';
    const TYPE_CATEGORY             = 'category';
    const TYPE_CUSTOMER_GROUP       = 'customer_group';
    
    /**
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     *
     * @param array $params
     */
    public function getDiscountStore(array $params = array())
    {
        $select = new Select();
        $select->from(array('dc' => 'discount_condition'), array())
                ->join(array('c' => 'customer'), 'c.customer_id = dc.customer_id', array(), Select::JOIN_LEFT)
                ->join(array('pl' => 'pricelist'), 'pl.pricelist_id = dc.pricelist_id', array(), Select::JOIN_LEFT)
                ->join(array('pb' => 'product_brand'), 'pb.brand_id = dc.brand_id', array(), Select::JOIN_LEFT)
                ->join(array('pg' => 'product_group'), 'pg.group_id = dc.customer_group_id', array(), Select::JOIN_LEFT)
                ->join(array('pm' => 'product_model'), 'pm.model_id = dc.model_id', array(), Select::JOIN_LEFT)
                ->join(array('cg' => 'customer_group'), 'cg.group_id = dc.customer_group_id', array(), Select::JOIN_LEFT)
                ->join(array('p' => 'product'), 'p.product_id = dc.product_id', array(), Select::JOIN_LEFT);

        $select->columns(array(
            'id' => new Expression('dc.id'),
            'pricelist_id' => new Expression('pl.pricelist_id'),
            'pricelist_reference' => new Expression('pl.reference'),
            'customer_group_id' => new Expression('dc.customer_group_id'),
            'customer_id' => new Expression('c.customer_id'),
            'brand_id' => new Expression('dc.brand_id'),
            'product_group_id' => new Expression('dc.product_group_id'),
            'model_id' => new Expression('dc.model_id'),
            'category_id' => new Expression('dc.category_id'),
            'product_id' => new Expression('dc.product_id'),
            'discount_1' => new Expression('dc.discount_1'),
            'discount_2' => new Expression('dc.discount_2'),
            'discount_3' => new Expression('dc.discount_3'),
            'discount_4' => new Expression('dc.discount_4'),
            'fixed_price' => new Expression('dc.fixed_price'),
            'valid_from' => new Expression('dc.valid_from'),
            'valid_till' => new Expression('dc.valid_till'),
        ), true);

        if (isset($params['customer_id'])) {
            $select->where(array('dc.customer_id' => $params['customer_id']));
        }

        if (isset($params['pricelist_id'])) {
            $select->where(array('dc.pricelist_id' => $params['pricelist_id']));
        }

        if (isset($params['pricelist_reference'])) {
            $select->where(array('dc.pricelist_reference' => $params['pricelist_reference']));
        }

        $sqlSource = new SqlSource($this->adapter, $select);
        $store = new Store($sqlSource);
        return $store;
    }
    
    
    
    /**
     * @throws \Exception
     * @return ArrayObject
     */
    public function getCustomerMatrix($customer_id, $pricelist)
    {
        // Step 1: get pricelist information

        $select = new Select();
        $select->from(array('pl' => 'pricelist'), array())
               ->where(array('pl.reference' => $pricelist))
               ->columns(array(
                   'pricelist_id' => new Expression('pl.pricelist_id'),
                   'pricelist_reference' => new Expression('pl.reference'),
                   'flag_enable_discount_condition' => new Expression('pl.flag_enable_discount_condition'),
                   'discount_condition_pricelist_id' => new Expression('pl.discount_condition_pricelist_id')
               ), false);
        
        $sql = new Sql($this->adapter);
        $results = $this->adapter->query($sql->getSqlStringForSqlObject($select))->execute();
        if ($results->count() != 1) {
            throw new \Exception(__METHOD__ . " Cannot get discount condition matrix, pricelist '$pricelist' does not exists");
        }
        $pricelist_data = $results->current();
        $flag_enable_discounts = $pricelist_data['flag_enable_discount_condition'];
        
        $matrix = new ArrayObject();
        
        // Only if pricelist has enabled discount conditions.
        if ($flag_enable_discounts == 1) {
            // Use the defined pricelist id.
            $pricelist_id = $pricelist_data['discount_condition_pricelist_id'];

            $store = $this->getDiscountStore();
            $select = $store->getSource()->getSelect();
            $select->where(array('dc.customer_id' => $customer_id));
            if ($pricelist_id === null) {
                $select->where->isNull('dc.pricelist_id');
            } else {
                $select->where->equalTo('dc.pricelist_id', $pricelist_id);
            }
            $conditions = $store->getData();
            
            $tmp = array();
            foreach ($conditions as $c) {
                $discounts = array(
                    'discount_1'  => $c['discount_1'],
                    'discount_2'  => $c['discount_2'],
                    'discount_3'  => $c['discount_3'],
                    'discount_4'  => $c['discount_4'],
                    'fixed_price' => $c['fixed_price']
                );

                $product_id         = $c['product_id'];
                $brand_id           = $c['brand_id'];
                $product_group_id   = $c['product_group_id'];
                $customer_id        = $c['customer_id'];
                $category_id        = $c['category_id'];
                
                if ($product_id != '') {
                    $matrix[self::TYPE_PRODUCT][$product_id] = $discounts;
                } elseif ($brand_id != '' && $product_group_id != '') {
                    $matrix[self::TYPE_BRAND_PRODUCT_GROUP][$brand_id][$product_group_id] = $discounts;
                } elseif ($brand_id != '') {
                    $matrix[self::TYPE_BRAND][$brand_id] = $discounts;
                } elseif ($product_group_id != '') {
                    $matrix[self::TYPE_PRODUCT_GROUP][$product_group_id] = $discounts;
                } elseif ($category_id != '') {
                    $matrix[self::TYPE_CATEGORY][$category_id] = $discounts;
                } elseif ($customer_id != '') {
                    $matrix[self::TYPE_CUSTOMER][$customer_id] = $discounts;
                }
            }
        }
        
        return $matrix;
    }
    
    

    /**
     * Set db adapter
     *
     * @param Adapter $adapter
     * @return CustomerDiscounts
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
}
