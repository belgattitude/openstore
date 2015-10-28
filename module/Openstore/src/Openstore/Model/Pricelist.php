<?php
namespace Openstore\Model;

use Openstore\Core\Model\AbstractModel;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

class Pricelist extends AbstractModel
{
    /**
     *
     * @return array
     */
    public function getPricelists()
    {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $select = $sql->select();

        $select->from(array('pl' => 'pricelist'), array());

        $select->columns(array(
            'pricelist_id'    => new Expression('pl.pricelist_id'),
            'reference'        => new Expression('pl.reference')
        ));

        $sql_string = $sql->getSqlStringForSqlObject($select);
        $results = $adapter->query($sql_string, array());

        return $results->toArray();
    }


    /**
     * Get associated customers
     * @param int $user_id
     * @return
     */
    public function getCustomers($user_id)
    {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);

        $select = $sql->select();

        $select->from(array('u' => 'user'), array())
                ->join(
                    array('us' => 'user_scope'),
                    new Expression("u.user_id = us.user_id"),
                    array(),
                    $select::JOIN_LEFT
                );
        $select->columns(array(
            'user_id'        => new Expression('u.user_id'),
            'customer_id'    => new Expression('us.customer_id'),
        ));

        $select->where('user_id = ?', $user_id);
        $sql_string = $sql->getSqlStringForSqlObject($select);

        //echo '<pre>';
        //var_dump($sql_string);die();
        //die();
        $results = $adapter->query($sql_string, $adapter::QUERY_MODE_EXECUTE)->toArray();
        return $results;
    }
}
