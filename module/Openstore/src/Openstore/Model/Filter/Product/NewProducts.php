<?php
namespace Openstore\Model\Filter\Product;

use Openstore\Core\Model\Browser\Filter\AbstractFilter;
use Zend\Db\Sql\Select;

class NewProducts extends AbstractFilter
{
    public function getName()
    {
        return 'new';
    }

    /**
     *
     * @param \Zend\Db\Sql\Select $select
     * @return \Zend\Db\Sql\Select
     */
    public function filter(Select $select)
    {
        //$this->getServiceLocator()->get('Openstore\Config');
        //$config['product']['filter']['minimum_date'];
        //die();
        $minimum_date = '2012-06-01';
        $select->where("(COALESCE(pl.new_product_min_date, '$minimum_date') <= COALESCE(ppl.available_at, p.available_at))");
        return $select;
    }


    /**
     * @param \Zend\Db\Sql\Select $select
     * @return \Openstore\Model\Filter\Product\NewProducts
     */
    public function addDefaultSortClause(Select $select)
    {
        $select->order([
            'ppl.available_at'    => $select::ORDER_DESCENDING,
            'p.available_at'    => $select::ORDER_DESCENDING,
            'p.reference'        => $select::ORDER_ASCENDING]);
        return $this;
    }
}
