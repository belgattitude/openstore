<?php
namespace Openstore\Core\Model\Browser\Filter;

use Zend\Db\Sql\Select;

interface FilterInterface
{
    /**
     * @return string
     */
    public function getName();
    
    /**
     *
     * @param \Zend\Db\Sql\Select $select
     * @return \Zend\Db\Sql\Select
     */
    public function filter(Select $select);
}
