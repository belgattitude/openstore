<?php

namespace OpenstoreSchema\Core\Entity;

use Doctrine\ORM\Mapping as ORM;
use ZfcRbac\Permission\PermissionInterface;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="permission",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_name_idx",columns={"name"}),
 *   },
 *   indexes={
 *     @ORM\Index(name="name_idx", columns={"name"}),
 *   },
 *   options={"comment" = "Custom permissions"}
 * )
 */
class Permission implements PermissionInterface
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="permission_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $permission_id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=128)
     */
    protected $name;

    /**
     * Constructor
     */
    public function __construct($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Get the permission identifier
     *
     * @return int
     */
    public function getId()
    {
        return $this->permission_id;
    }

    /**
     * Get the permission identifier
     *
     * @return int
     */
    public function getPermissionId()
    {
        return $this->permission_id;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->name;
    }
}
