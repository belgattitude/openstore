<?php

namespace OpenstoreSchema\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="revision",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_revison_log",columns={"related_table", "related_id", "created_at"}),
 *   },
 *   indexes={
 *     @ORM\Index(name="related_table_idx", columns={"related_table"}),
 *     @ORM\Index(name="related_id_idx", columns={"related_id"}),
 *     @ORM\Index(name="revision_idx", columns={"revision"}),
 *     @ORM\Index(name="created_at_idx", columns={"created_at"}),
 *   },
 *   options={"comment" = "Custom table revisions history"}
 * )
 */
class Revision
{
    /**
     * @ORM\Id
     * @ORM\Column(name="revision_id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $revision_id;

    /**
     * @ORM\Column(type="string", length=40, nullable=false, options={"comment" = "Related table"})
     */
    private $related_table;


    /**
     * @ORM\Column(type="bigint", nullable=false, options={"comment" = "Related table primary key"})
     */
    private $related_id;


    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Record creation timestamp"})
     */
    private $created_at;


    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\Column(type="string", length=40, nullable=true, options={"comment" = "Creator name"})
     */
    private $created_by;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default"=0, "comment"="Whether the saving includes a new revision"})
     */
    private $flag_revision_updated;



    /**
     * @ORM\Column(type="integer", nullable=true, options={"comment" = "Optional revision number, revision differ from version as it acts like a milestone"})
     */
    private $revision;


    /**
     * @ORM\Column(type="string", length=150, nullable=true, options={"comment" = "Log message, reason of change"})
     */
    private $message;


    /**
     * @ORM\Column(type="string", length=64000, nullable=false, options={"comment" = "Previous data, stored as json"})
     */
    private $previous_data;


    /**
     * @ORM\Column(type="string", length=64000, nullable=false, options={"comment" = "Data at time of saving, stored as json"})
     */
    private $current_data;


    public function __construct()
    {
    }


    /**
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     *
     * @param string $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }


    /**
     * Return creator username
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * Set creator username
     * @param string $created_by
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;
    }
}
