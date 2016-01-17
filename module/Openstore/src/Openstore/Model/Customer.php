<?php

namespace Openstore\Model;

use Openstore\Core\Model\AbstractModel;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

class Customer extends AbstractModel
{
    /**
     *
     * @param int $customer_id
     * @return array
     */
    public function getCustomerPricelists($customer_id)
    {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $select = $sql->select();

        $select->from(['c' => 'customer'], [])
                ->join(['cpl' => 'customer_pricelist'], new Expression("c.customer_id = cpl.user_id"), [])
                ->join(['pl' => 'pricelist'], new Expression('pl.pricelist_id = cpl.pricelist_id'), []);

        $select->columns([
            //'user_id'     => new Expression('u.user_id'),
            'pricelist_id' => new Expression('pl.pricelist_id'),
            'reference' => new Expression('pl.reference')
        ]);

        $select->where(["u.customer_id" => $customer_id]);

        $sql_string = $sql->getSqlStringForSqlObject($select);
        $results = $adapter->query($sql_string, []);
        return $results->toArray();
    }

    /**
     *
     * @param integer $user_id
     * @return array
     */
    public function getUserRoles($user_id)
    {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $select = $sql->select();

        $select->from(['u' => 'user'], [])
                ->join(['ur' => 'user_role'], new Expression("u.user_id = ur.user_id"), [])
                ->join(['r' => 'role'], new Expression('r.role_id = ur.role_id'), []);

        $select->columns([
            //'user_id'     => new Expression('u.user_id'),
            'role_id' => new Expression('r.role_id'),
            'reference' => new Expression('r.reference')
        ]);

        $select->where(["u.user_id" => $user_id]);

        $sql_string = $sql->getSqlStringForSqlObject($select);
        $results = $adapter->query($sql_string, []);
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

        $select->from(['u' => 'user'], [])
                ->join(['us' => 'user_scope'], new Expression("u.user_id = us.user_id"), [], $select::JOIN_LEFT);
        $select->columns([
            'user_id' => new Expression('u.user_id'),
            'customer_id' => new Expression('us.customer_id'),
        ]);

        $select->where('user_id = ?', $user_id);
        $sql_string = $sql->getSqlStringForSqlObject($select);

        //echo '<pre>';
        //var_dump($sql_string);die();
        //die();
        $results = $adapter->query($sql_string, $adapter::QUERY_MODE_EXECUTE)->toArray();
        return $results;
    }
}
