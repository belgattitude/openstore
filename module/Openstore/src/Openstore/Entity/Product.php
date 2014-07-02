<?php

namespace Openstore\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="product",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_reference_idx",columns={"reference", "brand_id", "flag_active"}),
 *     @ORM\UniqueConstraint(name="unique_legacy_mapping_idx",columns={"legacy_mapping"}),
 *     @ORM\UniqueConstraint(name="unique_slug_idx",columns={"slug"})
 *   }, 
 *   indexes={
 *     @ORM\Index(name="title_idx", columns={"title"}),
 *     @ORM\Index(name="reference_idx", columns={"reference"}),
 *     @ORM\Index(name="display_reference_idx", columns={"display_reference"}),
 *     @ORM\Index(name="description_idx", columns={"description"}),
 *     @ORM\Index(name="characteristic_idx", columns={"characteristic"}),
 *     @ORM\Index(name="keywords_idx", columns={"keywords"}),
 *     @ORM\Index(name="slug_idx", columns={"slug"}),
 *   },
 *   options={"comment" = "Product table"}
 * )
 * @Gedmo\SoftDeleteable(fieldName="deleted_at")
 */
class Product implements InputFilterAwareInterface {

    /**
     * @var \Zend\InputFilter\InputFilterInterface $inputFilter
     */
    protected $inputFilter;

    /**
     * @ORM\OneToMany(targetEntity="ProductTranslation", mappedBy="product_id")
     * */
    private $translations;

    /**
     * @ORM\Id
     * @ORM\Column(name="product_id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $product_id;

    /**
     * @ORM\Column(type="string", length=60, nullable=false, options={"comment" = "Unique reference"})
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=60, nullable=true, options={"comment" = "Displayable reference, common for search and display"})
     */
    private $display_reference;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="product_id", onDelete="CASCADE")
     */
    private $parent_id;

    /**
     * 
     * @ORM\ManyToOne(targetEntity="ProductBrand", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="brand_id", onDelete="CASCADE", nullable=true)
     */
    private $brand_id;

    /**
     * 
     * @ORM\ManyToOne(targetEntity="ProductGroup", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="group_id", referencedColumnName="group_id", onDelete="CASCADE", nullable=true)
     */
    private $group_id;

    /**
     * 
     * @ORM\ManyToOne(targetEntity="ProductModel", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="model_id", referencedColumnName="model_id", onDelete="CASCADE", nullable=true)
     */
    private $model_id;

    /**
     * 
     * @ORM\ManyToOne(targetEntity="ProductCategory", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="category_id", onDelete="CASCADE", nullable=true)
     */
    private $category_id;

    /**
     * Type id
     * @ORM\ManyToOne(targetEntity="ProductType", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="type_id", referencedColumnName="type_id", onDelete="CASCADE", nullable=true)
     */
    private $type_id;

    /**
     * Sales unit
     * @ORM\ManyToOne(targetEntity="ProductUnit", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="unit_id", onDelete="CASCADE", nullable=true)
     */
    private $unit_id;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=255, nullable=true, options={"comment" = "Unique slug for this record"})
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
     * @ORM\Column(type="string", length=15000, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $characteristic;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $keywords;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=true, options={"comment"="Volume per sales unit in m3"})
     */
    private $volume;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=true, options={"comment"="Weight per sales unit in Kg"})
     */
    private $weight;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=true, options={"comment"="Length per sales unit in meter"})
     */
    private $length;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=true, options={"comment"="Heigth per sales unit in meter"})
     */
    private $height;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=true, options={"comment"="Width per sales unit in meter"})
     */
    private $width;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=6, nullable=true, options={"comment"="Packaging items per box"})
     */
    private $pack_qty_box;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=6, nullable=true, options={"comment"="Packaging items per carton"})
     */
    private $pack_qty_carton;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=6, nullable=true, options={"comment"="Packaging items per master carton"})
     */
    private $pack_qty_master_carton;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=6, nullable=true, options={"comment"="Packaging items per palet"})
     */
    private $pack_qty_palet;

    /**
     * @ORM\Column(type="string", length=13, nullable=true, options={"comment"="EAN13 barcode"})
     */
    private $barcode_ean13;

    /**
     * @ORM\Column(type="string", length=12, nullable=true, options={"comment"="UPCA barcode"})
     */
    private $barcode_upca;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default"=1, "comment"="Whether the product is active in public website"})
     */
    private $flag_active;

    /**
     * @ORM\Column(type="date", nullable=true, options={"comment" = "Date on which product was actived/available"})
     */
    private $activated_at;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $icon_class;

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
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Record deletion date"})
     */
    private $deleted_at;

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

    public function __construct() {

        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();

        /**
         * Default value for flag_active
         */
        $this->flag_active = true;
    }

    /**
     * 
     * @param integer $product_id
     */
    public function setProductId($product_id) {
        $this->product_id = $product_id;
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getProductId() {
        return $this->product_id;
    }

    /**
     * Set reference
     * @param string $reference
     */
    public function setReference($reference) {
        $this->reference = $reference;
        return $this;
    }

    /**
     * Return reference 
     * @return string
     */
    public function getReference() {
        return $this->reference;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug) {
        $this->slug = $slug;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getSlug() {
        return $this->slug;
    }

    /**
     * 
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * 
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * 
     * @param float $type_id
     * @return \Openstore\Entity\Product
     */
    function setTypeId($type_id) {
        $this->type_id = $type_id;
        return $this;
    }

    /**
     * @return integer
     */
    function getTypeId() {
        return $this->type_id;
    }

    /**
     * 
     * @param float $unit_id
     * @return \Openstore\Entity\Product
     */
    function setUnitId($unit_id) {
        $this->unit_id = $unit_id;
        return $this;
    }

    /**
     * @return integer
     */
    function getUnitId() {
        return $this->unit_id;
    }

    /**
     * Set volume
     * @return Product
     */
    function setVolume($volume) {
        $this->volume = $volume;
        return $this;
    }

    /**
     * 
     * @return float
     */
    function getVolume() {
        return $this->volume;
    }

    /**
     * Set weight
     * @return Product
     */
    function setWeight($weight) {
        $this->weight = $weight;
        return $this;
    }

    /**
     * 
     * @return float
     */
    function getWeight() {
        return $this->weight;
    }

    /**
     * Set length
     * @return Product
     */
    function setLength($length) {
        $this->length = $length;
        return $this;
    }

    /**
     * 
     * @return float
     */
    function getLength() {
        return $this->length;
    }

    /**
     * Set height
     * @return Product
     */
    function setHeight($height) {
        $this->height = $height;
        return $this;
    }

    /**
     * 
     * @return decimal
     */
    function getHeight() {
        return $this->height;
    }

    /**
     * Set width
     * @return Product
     */
    function setWidth($width) {
        $this->width = $with;
        return $this;
    }

    /**
     * 
     * @return decimal
     */
    function getWidth() {
        return $this->width;
    }

    /**
     * Set barcode_ean13
     * @param string $barcode_ean13
     * @return Product
     */
    function setBarcodeEan13($barcode_ean13) {
        $this->barcode_ean13 = $barcode_ean13;
        return $this;
    }

    /**
     * 
     * @return string
     */
    function getBarcodeEan13() {
        return $this->barcode_ean13;
    }

    /**
     * 
     * @return string
     */
    public function setIconClass($icon_class) {
        $this->icon_class = $icon_class;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getIconClass() {
        return $this->icon_class;
    }

    /**
     * 
     * @return boolean
     */
    public function getFlagActive() {
        return (boolean) $this->flag_active;
    }

    /**
     * 
     */
    public function setFlagActive($flag_active) {
        $this->flag_active = $flag_active;
        return $this;
    }

    /**
     * 
     * @return date
     */
    public function getActivatedAt() {
        return $this->activated_at;
    }

    /**
     * @param string $activated_at date in Y-m-d H:i:s format
     */
    public function setActivatedAt($activated_at) {
        $this->activated_at = $activated_at;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getCreatedAt() {
        return $this->created_at;
    }

    /**
     * 
     * @param string $created_at
     */
    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getUpdatedAt() {
        return $this->updated_at;
    }

    /**
     * 
     * @param string $updated_at
     */
    public function setUpdatedAt($updated_at) {
        $this->updated_at = $updated_at;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getDeletedAt() {
        return $this->deleted_at;
    }

    /**
     * 
     * @param string $updated_at
     */
    public function setDeletedAt($deleted_at) {
        $this->deleted_at = $deleted_at;
        return $this;
    }

    /**
     * Return creator username
     * @return string
     */
    public function getCreatedBy() {
        return $this->created_by;
    }

    /**
     * Set creator username
     * @param string $created_by
     */
    public function setCreatedBy($created_by) {
        $this->created_by = $created_by;
        return $this;
    }

    /**
     * Return last updater username
     * @return string
     */
    public function getUpdatedBy() {
        return $this->updated_by;
    }

    /**
     * Set the last updater username
     * @param string $updated_by
     */
    public function setUpdatedBy($updated_by) {
        $this->updated_by = $updated_by;
        return $this;
    }

    /**
     * Return legacy mapping 
     * @return string $legacy_mapping
     */
    public function getLegacyMapping() {
        return $this->legacy_mapping;
    }

    /**
     * Set a legacy mapping for this record
     * @param string $legacy_mapping
     */
    public function setLegacyMapping($legacy_mapping) {
        $this->legacy_mapping = $legacy_mapping;
        return $this;
    }

    /**
     * Set legacy synchro time
     * @param string $legacy_mapping
     */
    public function setLegacySynchroAt($legacy_synchro_at) {
        $this->legacy_synchro_at = $legacy_synchro_at;
        return $this;
    }

    /**
     * Return legacy synchro timestamp 
     * @return string 
     */
    public function getLegacySynchroAt() {
        return $this->legacy_synchro_at;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy() {
        return get_object_vars($this);
    }

    /**
     * 
     * @return string
     */
    public function __toString() {
        return $this->getTitle();
    }

    /**
     * Magic getter to expose protected properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        return $this->$property;
    }

    /**
     * Magic setter to save protected properties.
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) {
        $this->$property = $value;
    }

    /**
     * 
     * @param \Zend\InputFilter\InputFilterInterface $inputFilter
     */
    public function setInputFilter(InputFilterInterface $inputFilter) {
        $this->inputFiler = $inputFilter;
        return $this;
    }

    /**
     * 
     * @return \Zend\InputFilter\InputFilterInterface $inputFilter
     */
    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                        'name' => 'reference',
                        'required' => true,
                        'filters' => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                        'validators' => array(
                            array(
                                'name' => 'StringLength',
                                'options' => array(
                                    'encoding' => 'UTF-8',
                                    'min' => 1,
                                    'max' => 60,
                                ),
                            ),
                        ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}
