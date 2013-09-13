<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Openstore\Authorize\Provider\Identity;

use BjyAuthorize\Provider\Identity\ZfcUserZendDb;
use BjyAuthorize\Exception\InvalidRoleException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Permissions\Acl\Role\RoleInterface;
use ZfcUser\Service\User;

class OpenstoreDb extends ZfcUserZendDb
{


    public function getIdentityRoles()
    {
        $authService = $this->userService->getAuthService();

        if (! $authService->hasIdentity()) {
            return array($this->getDefaultRole());
        }

        // get roles associated with the logged in user
        $sql    = new Sql($this->adapter);
        $select = $sql->select()
					->from(array('ur' => 'user_role'), array())
					->join(array('r' => 'role'), new Expression('ur.role_id = r.role_id'),
						array())
					->columns(array(
						'reference' => new Expression('r.reference'),
						'role_id' => new Expression('ur.role_id')
					));
					
		
        $where  = new Where();
        $where->equalTo('user_id', $authService->getIdentity()->getId());
        $results = $sql->prepareStatementForSqlObject($select->where($where))->execute();
        $roles     = array();
        foreach ($results as $i) {
            $roles[] = $i['reference'];
        }

        return $roles;
    }

    /**
     * @return string|\Zend\Permissions\Acl\Role\RoleInterface
     */
    public function getDefaultRole()
    {
        return $this->defaultRole;
    }

    /**
     * @param string|\Zend\Permissions\Acl\Role\RoleInterface $defaultRole
     *
     * @throws \BjyAuthorize\Exception\InvalidRoleException
     */
    public function setDefaultRole($defaultRole)
    {
        if (! ($defaultRole instanceof RoleInterface || is_string($defaultRole))) {
            throw InvalidRoleException::invalidRoleInstance($defaultRole);
        }

        $this->defaultRole = $defaultRole;
    }
	
	
}
