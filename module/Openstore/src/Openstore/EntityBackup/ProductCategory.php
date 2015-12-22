<?php

namespace OpenstoreSchema\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(
 *   name="product_category",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_reference_idx",columns={"reference"}),
 *     @ORM\UniqueConstraint(name="unique_legacy_mapping_idx",columns={"legacy_mapping"}),
 *     @ORM\UniqueConstraint(name="unique_slug_idx",columns={"slug"})
 *   },
 *   indexes={
 *     @ORM\Index(name="title_idx", columns={"title"}),
 *     @ORM\Index(name="description_idx", columns={"description"}),
 *     @ORM\Index(name="slug_idx", columns={"slug"}),
 *     @ORM\Index(name="lft_idx", columns={"lft"}),
 *     @ORM\Index(name="rgt_idx", columns={"rgt"}),
 *     @ORM\Index(name="lvl_idx", columns={"lvl"}),
 *     @ORM\Index(name="breadcrumb_idx", columns={"breadcrumb"}),
 *     @ORM\Index(name="alt_mapping_reference_idx", columns={"alt_mapping_reference"}),
 *   },
 *   options={"comment" = "Product category table"}
 * )
 * @ORM\Entity(repositoryClass="OpenstoreSchema\Core\Entity\Repository\ProductCategoryRepository")
 */
class ProductCategory
{
    /**
     * @var \Zend\InputFilter\InputFilterInterface $inputFilter
     */
    protected $inputFilter;

    /**
     * @ORM\OneToMany(targetEntity="ProductBrandTranslation", mappedBy="brand_id")
     * */
    private $translations;

    /**
     * @ORM\Id
     * @ORM\Column(name="category_id", type="integer", nullable=false,  options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $category_id;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    private $reference;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=64, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=1500, nullable=true)
     */
    private $breadcrumb;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true, "comment"="Relative sort index"})
     */
    private $sort_index;

    /**
     * @ORM\Column(type="bigint", nullable=true, options={"unsigned"=true, "comment"="Global sort index"})
     */
    private $global_sort_index;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $icon_class;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer",  options={"unsigned"=true})
     */
    private $rgt;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="ProductCategory", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="category_id", onDelete="CASCADE")
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
     * @ORM\OneToMany(targetEntity="ProductCategory", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\Column(type="string", length=10, nullable=true, options={"comment" = "Alternative free reference code"})
     */
    private $alt_mapping_reference;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\Column(type="string",length=40,nullable=true)
     */
    private $created_by;

    /**
     * @Gedmo\Blameable(on="update")
     * @ORM\Column(type="string",length=40,nullable=true)
     */
    private $updated_by;

    /**
     * @ORM\Column(type="string",length=40,nullable=true)
     */
    protected $legacy_mapping;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $legacy_synchro_at;

    public function __construct()
    {
        $this->children = new ArrayCollection();
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
     *
     * @param string $reference
     */
    public function setReference($reference)
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
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
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
     * @param integer $sort_index
     * @return ProductCategory
     */
    public function setSortIndex($sort_index)
    {
        $this->sort_index = $sort_index;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getSortIndex()
    {
        return $this->sort_index;
    }

    /**
     *
     * @param integer $global_sort_index
     * @return ProductCategory
     */
    public function setGlobalSortIndex($global_sort_index)
    {
        $this->global_sort_index = $global_sort_index;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getGlobalSortIndex()
    {
        return $this->global_sort_index;
    }

    /**
     *
     * @return string
     */
    public function setIconClass($icon_class)
    {
        $this->icon_class = $icon_class;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getIconClass()
    {
        return $this->icon_class;
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
     *
     * @param string|int $alt_mapping_reference
     */
    public function setAltMappingReference($alt_mapping_reference)
    {
        $this->alt_mapping_reference = $alt_mapping_reference;
    }

    /**
     *
     * @return string|int
     */
    public function getAltMappingReference()
    {
        return $this->alt_mapping_reference;
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
    }

    /**
     * Set legacy synchro time
     * @param string $legacy_mapping
     */
    public function setLegacySynchroAt($legacy_synchro_at)
    {
        $this->legacy_synchro_at = $legacy_synchro_at;
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
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
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
