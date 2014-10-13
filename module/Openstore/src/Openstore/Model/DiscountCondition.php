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

class DiscountCondition implements AdapterAwareInterface {

    /**
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * 
     * @param array $params
     */
    public function getDiscountStore(array $params = array()) {

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
     * Set db adapter
     *
     * @param Adapter $adapter
     * @return CustomerDiscounts
     */
    public function setDbAdapter(Adapter $adapter) {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * 
     * @return Adapter
     */
    public function getDbAdapter() {
        return $this->adapter;
    }

}
