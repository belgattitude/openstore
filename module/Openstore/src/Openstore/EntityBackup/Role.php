<?php

namespace OpenstoreSchema\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Rbac\Role\HierarchicalRoleInterface;
use ZfcRbac\Permission\PermissionInterface;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="OpenstoreSchema\Core\Entity\Repository\RoleRepository")
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(
 *   name="role",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_name_idx",columns={"name"}),
 *   },
 *   indexes={
 *     @ORM\Index(name="lft_idx", columns={"lft"}),
 *     @ORM\Index(name="rgt_idx", columns={"rgt"}),
 *   },
 *   options={"comment" = "Access roles"}
 * )
 *
 */
class Role implements HierarchicalRoleInterface
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(name="role_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $role_id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=48)
     */
    protected $name;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $rgt;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="role_id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="bigint", nullable=true, options={"unsigned"=true})
     */
    private $root;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer",  options={"unsigned"=true})
     */
    private $level;

    /**
     * @ORM\OneToMany(targetEntity="Role", mappedBy="parent")
     */
    private $children;

    /**
     * @var PermissionInterface[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Permission", indexBy="name", fetch="EAGER")
     * @ORM\JoinTable(name="role_permission",
     *      joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="role_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="permission_id", referencedColumnName="permission_id")}
     *      )
     */
    protected $permissions;

    /**
     * @ORM\ManyToMany(targetEntity="OpenstoreSchema\Core\Entity\User", mappedBy="roles")
     */
    private $users;

    /**
     * Init the Doctrine collection
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * Get the role identifier
     *
     * @return int
     */
    public function getId()
    {
        return $this->role_id;
    }

    /**
     * Get the role identifier
     *
     * @return int
     */
    public function getRoleId()
    {
        return $this->role_id;
    }

    /**
     * Set the role name
     *
     * @param  string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Get the role name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function addChild(HierarchicalRoleInterface $child)
    {
        $this->children[] = $child;
    }

    /**
     * {@inheritDoc}
     */
    public function addPermission($permission)
    {
        if (is_string($permission)) {
            $permission = new Permission($permission);
        }

        $this->permissions[(string) $permission] = $permission;
    }

    /**
     * {@inheritDoc}
     */
    public function hasPermission($permission)
    {
        // This can be a performance problem if your role has a lot of permissions. Please refer
        // to the cookbook to an elegant way to solve this issue

        return isset($this->permissions[(string) $permission]);
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getLeft()
    {
        return $this->lft;
    }

    public function getRight()
    {
        return $this->rgt;
    }

    /**
     * {@inheritDoc}
     */
    /*
      public function getChildren()
      {
      return $this->children;
      }
     */

    /**
     * {@inheritDoc}
     */
    public function hasChildren()
    {
        return !$this->children->isEmpty();
    }

    /**
     * Get users
     *
     * @return User
     */
    public function getUsers()
    {
        return $this->users;
    }
}
