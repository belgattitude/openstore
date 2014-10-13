<?php

namespace Openstore\Model;

use Openstore\Core\Model\AbstractModel;
use Openstore\Core\Model\BrowsableInterface;
use Openstore\Model\Browser\UserBrowser;
use Openstore\Entity;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Stdlib\Hydrator;

class Customer extends AbstractModel {

    /**
     * 
     * @param int $customer_id
     * @return array
     */
    function getCustomerPricelists($customer_id) {

        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $select = $sql->select();

        $select->from(array('c' => 'customer'), array())
                ->join(array('cpl' => 'customer_pricelist'), new Expression("c.customer_id = cpl.user_id"), array())
                ->join(array('pl' => 'pricelist'), new Expression('pl.pricelist_id = cpl.pricelist_id'), array());

        $select->columns(array(
            //'user_id'		=> new Expression('u.user_id'), 
            'pricelist_id' => new Expression('pl.pricelist_id'),
            'reference' => new Expression('pl.reference')
        ));

        $select->where(array("u.customer_id" => $customer_id));

        $sql_string = $sql->getSqlStringForSqlObject($select);
        $results = $adapter->query($sql_string, array());
        return $results->toArray();
    }

    /**
     * 
     * @param integer $user_id
     * @return array
     */
    function getUserRoles($user_id) {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $select = $sql->select();

        $select->from(array('u' => 'user'), array())
                ->join(array('ur' => 'user_role'), new Expression("u.user_id = ur.user_id"), array())
                ->join(array('r' => 'role'), new Expression('r.role_id = ur.role_id'), array());

        $select->columns(array(
            //'user_id'		=> new Expression('u.user_id'), 
            'role_id' => new Expression('r.role_id'),
            'reference' => new Expression('r.reference')
        ));

        $select->where(array("u.user_id" => $user_id));

        $sql_string = $sql->getSqlStringForSqlObject($select);
        $results = $adapter->query($sql_string, array());
        return $results->toArray();
    }

    /**
     * Get associated customers
     * @param int $user_id
     * @return 
     */
    function getCustomers($user_id) {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);

        $select = $sql->select();

        $select->from(array('u' => 'user'), array())
                ->join(array('us' => 'user_scope'), new Expression("u.user_id = us.user_id"), array(), $select::JOIN_LEFT);
        $select->columns(array(
            'user_id' => new Expression('u.user_id'),
            'customer_id' => new Expression('us.customer_id'),
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
