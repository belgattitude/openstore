<?php
namespace Openstore\Model\Filter\Product;

use Openstore\Core\Model\Browser\Filter\AbstractFilter;
use Zend\Db\Sql\Select;

class PromoProducts extends AbstractFilter
{
    public function getName()
    {
        return 'promos';
    }


    /**
     *
     * @param \Zend\Db\Sql\Select $select
     * @return \Zend\Db\Sql\Select
     */
    public function filter(Select $select)
    {
        $select->where("(ppl.is_promotional = 1)");
        return $select;
    }


    public function addDefaultSortClause(Select $select)
    {
        $select->order([
            'ppl.discount_1'    => $select::ORDER_DESCENDING,
            'p.reference'        => $select::ORDER_ASCENDING]);
        return $this;
    }
}
