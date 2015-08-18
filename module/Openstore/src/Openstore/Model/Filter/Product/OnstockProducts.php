<?php
namespace Openstore\Model\Filter\Product;

use Openstore\Core\Model\Browser\Filter\AbstractFilter;
use Zend\Db\Sql\Select;

class OnstockProducts extends AbstractFilter
{
    public function getName()
    {
        return 'onstock';
    }
    
    
    /**
     *
     * @param \Zend\Db\Sql\Select $select
     * @return \Zend\Db\Sql\Select
     */
    public function filter(Select $select)
    {
        $select->where("(ps.available_stock > 0)");
        return $select;
    }
}
