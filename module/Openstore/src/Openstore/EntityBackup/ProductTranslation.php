<?php

namespace OpenstoreSchema\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="product_translation",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_legacy_mapping_idx",columns={"legacy_mapping"}),
 *     @ORM\UniqueConstraint(name="unique_translation_idx",columns={"product_id", "lang"})
 *   },
 *   indexes={
 *     @ORM\Index(name="title_idx", columns={"title"}),
 *     @ORM\Index(name="description_idx", columns={"description"}),
 *     @ORM\Index(name="characteristic_idx", columns={"characteristic"}),
 *     @ORM\Index(name="keywords_idx", columns={"keywords"}),
 *     @ORM\Index(name="slug_idx", columns={"slug"}),
 *     @ORM\Index(name="revision_idx", columns={"revision"}),
 *   },
 *   options={
 *      "comment" = "Product translation table"
 *   }
 * )
 *
 * "charset"="utf8mb4",
 * "collate"="utf8mb4_unicode_ci",
 *
 *
 */
class ProductTranslation
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true, "comment" = "Primary key"})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="translations", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", onDelete="CASCADE", nullable=false)
     */
    private $product_id;

    /**
     * @ORM\ManyToOne(targetEntity="Language", inversedBy="product_translations", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="lang", referencedColumnName="lang", onDelete="RESTRICT", nullable=false)
     * //options={"customSchemaOptions"={ "charset"="utf8" }})
     */
    private $lang;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=150, nullable=true, options={"comment" = "Unique slug for this record"})
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $invoice_title;

    /**
     * @ORM\Column(type="string", length=5000, nullable=true)
     */
    private $description;


    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $characteristic;

    /**
     * @ORM\Column(type="string", length=5000, nullable=true, options={"comment" = "Specifications"})
     */
    private $specs;


    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $keywords;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default"=1, "unsigned"=true, "comment" = "Translation revision number"})
     */
    private $revision;


    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Record creation timestamp"})
     */
    private $created_at;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Record last update timestamp"})
     */
    private $updated_at;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\Column(type="string", length=40, nullable=true, options={"comment" = "Creator name"})
     */
    private $created_by;

    /**
     * @Gedmo\Blameable(on="update")
     * @ORM\Column(type="string", length=40, nullable=true, options={"comment" = "Last updater name"})
     */
    private $updated_by;

    /**
     * @ORM\Column(type="string",length=40,nullable=true, options={"comment" = "Unique reference of this record taken from legacy system"})
     */
    protected $legacy_mapping;

    /**
     * @ORM\Column(type="datetime",nullable=true, options={"comment" = "Last synchro timestamp"})
     */
    protected $legacy_synchro_at;

    public function __construct()
    {
    }

    /**
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     *
     * @param string $specs
     */
    public function setSpecs($specs)
    {
        $this->specs = $specs;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getSpecs()
    {
        return $this->specs;
    }


    /**
     *
     * @param integer $product_id
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getProductId()
    {
        return $this->product_id;
    }


    /**
     *
     * @param integer $revision
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getRevision()
    {
        return $this->revision;
    }


    /**
     *
     * @param integer $lang_id
     */
    public function setLangId($lang_id)
    {
        $this->lang_id = $lang_id;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getLangId()
    {
        return $this->lang_id;
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
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     *
     * @param string $updated_at
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
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
        return $this;
    }

    /**
     * Return last updater username
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updated_by;
    }

    /**
     * Set the last updater username
     * @param string $updated_by
     */
    public function setUpdatedBy($updated_by)
    {
        $this->updated_by = $updated_by;
        return $this;
    }

    /**
     * Return legacy mapping
     * @return string $legacy_mapping
     */
    public function getLegacyMapping()
    {
        return $this->legacy_mapping;
    }

    /**
     * Set a legacy mapping for this record
     * @param string $legacy_mapping
     */
    public function setLegacyMapping($legacy_mapping)
    {
        $this->legacy_mapping = $legacy_mapping;
        return $this;
    }

    /**
     * Set legacy synchro time
     * @param string $legacy_mapping
     */
    public function setLegacySynchroAt($legacy_synchro_at)
    {
        $this->legacy_synchro_at = $legacy_synchro_at;
        return $this;
    }

    /**
     * Return legacy synchro timestamp
     * @return string
     */
    public function getLegacySynchroAt()
    {
        return $this->legacy_synchro_at;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * Magic getter to expose protected properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }

    /**
     * Magic setter to save protected properties.
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }
}
