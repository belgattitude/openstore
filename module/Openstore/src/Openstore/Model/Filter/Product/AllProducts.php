<?php
namespace Openstore\Model\Filter\Product;

use Openstore\Core\Model\Browser\Filter\AbstractFilter;
use Zend\Db\Sql\Select;

class AllProducts extends AbstractFilter
{
    public function getName()
    {
        return 'all';
    }

    /**
     *
     * @param \Zend\Db\Sql\Select $select
     * @return \Zend\Db\Sql\Select
     */
    public function filter(Select $select)
    {
        // Simply do nothing
        return $select;
    }
}
