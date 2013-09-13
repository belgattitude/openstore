<?php

namespace Openstore\Entity;

use BjyAuthorize\Acl\HierarchicalRoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="role")
 *
 */
class Role implements HierarchicalRoleInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $role_id;

    /**
     * @var string
     * @ORM\Column(type="string", length=80, unique=true, nullable=true)
     */
    protected $reference;

    /**
     * @var Role
     * @ORM\ManyToOne(targetEntity="Openstore\Entity\Role")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="role_id", onDelete="RESTRICT", nullable=true)	 
     
     */
    protected $parent_id;

    /**
     * Get the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->role_id;
    }

    /**
     * Set the id.
     *
     * @param int $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->role_id = (int) $id;
    }

    /**
     * Get the reference name.
	 * 
	 * WARNING
     * FOR BjyAuthorize\Provider\Role\ObjectRepositoryProvider
	 * LINE 50 !!!
	 * 
     * @return int
     */
    public function getRoleId()
    {
        return $this->reference;
    }

    /**
     * Set the id.
     *
     * @param int $id
     *
     * @return void
     */
    public function setRoleId($reference)
    {
        $this->reference = $reference;
    }
	
	
    /**
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set the role id.
     *
     * @param string $roleId
     *
     * @return void
     */
    public function setReference($reference)
    {
        $this->reference = (string) $reference;
    }

    /**
     * Get the parent role
     *
     * @return Role
     */
    public function getParent()
    {
        return $this->parent_id;
    }

    /**
     * Set the parent role.
     *
     * @param Role $role
     *
     * @return void
     */
    public function setParent(Role $parent)
    {
        $this->parent_id = $parent;
    }
}