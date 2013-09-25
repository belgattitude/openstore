<?php
namespace Openstore\Model;
use Openstore\Core\Model\AbstractModel;
use Openstore\Core\Model\BrowsableInterface;
use Openstore\Model\Browser\UserBrowser;
use Openstore\Entity;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

use Zend\Stdlib\Hydrator;

class User extends AbstractModel implements BrowsableInterface {

	
	/**
	 * 
	 */
	function getDoctrineRepository()
	{
		
	}
	/**
	 * 
	 * @param int $id user id
	 * @return \Openstore\Model\Entity\User
	 */
	function getDoctrineEntity($id=null) {
		if ($id === null) {
			$entity = new Entity\User();
		} else {
			$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
			$entity = $em->getRepository('Openstore\Entity\User')->find($id);			
		}
		return $entity;
	}
	
	/**
	 * @return \Openstore\Model\Browser\UserBrowser
	 */
	function getBrowser()
	{
		return new UserBrowser($this);
	}
	
	/**
	 * 
	 * @param int $user_id
	 * @return array
	 */
	function getUserPricelists($user_id)
	{

		$adapter = $this->adapter;
		$sql = new Sql($adapter);
		$select = $sql->select();
		
		$select->from(array('u' => 'user'),  array())
				->join(array('upl' => 'user_pricelist'), 
							new Expression("u.user_id = upl.user_id"), 
							array())
				->join(array('pl' => 'pricelist'),
							new Expression('pl.pricelist_id = upl.pricelist_id'),
							array());
				
		$select->columns(array(
			//'user_id'		=> new Expression('u.user_id'), 
			'pricelist_id'	=> new Expression('upl.pricelist_id'), 
			'reference'		=> new Expression('pl.reference')
		));
		
		$select->where(array("u.user_id" => $user_id));
		
		$sql_string = $sql->getSqlStringForSqlObject($select);
		$results = $adapter->query($sql_string, array());
		return $results->toArray();
		
	}
	
	
	/**
	 * 
	 * @param integer $user_id
	 * @return array
	 */
	function getUserRoles($user_id)
	{
		$adapter = $this->adapter;
		$sql = new Sql($adapter);
		$select = $sql->select();
		
		$select->from(array('u' => 'user'),  array())
				->join(array('ur' => 'user_role'), 
							new Expression("u.user_id = ur.user_id"), 
							array())
				->join(array('r' => 'role'),
							new Expression('r.role_id = ur.role_id'),
							array());
				
		$select->columns(array(
			//'user_id'		=> new Expression('u.user_id'), 
			'role_id'	=> new Expression('r.role_id'), 
			'reference'		=> new Expression('r.reference')
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
	function getCustomers($user_id)
	{
		$adapter = $this->adapter;
		$sql = new Sql($adapter);
		
		$select = $sql->select();
		
		$select->from(array('u' => 'user'),  array())
				->join(array('us' => 'user_scope'), 
							new Expression("u.user_id = us.user_id"), 
							array(), $select::JOIN_LEFT);
		$select->columns(array(
			'user_id'		=> new Expression('u.user_id'), 
			'customer_id'	=> new Expression('us.customer_id'), 
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
