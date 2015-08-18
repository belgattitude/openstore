<?php
namespace Openstore\Model\Filter\Product;

use Openstore\Core\Model\Browser\Filter\AbstractFilter;
use Zend\Db\Sql\Select;

class FavouriteProducts extends AbstractFilter
{
    public function getName()
    {
        return 'favourite';
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
