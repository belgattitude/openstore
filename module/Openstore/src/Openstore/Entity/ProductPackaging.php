<?php

namespace Openstore\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="product_packaging",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_product_packaging_idx",columns={"type_id", "product_id"}),
 *     @ORM\UniqueConstraint(name="unique_legacy_mapping_idx",columns={"legacy_mapping"}),
 *   }, 
 *   indexes={
 *   },
 *   options={"comment" = "Product packaging information"}
 * )
 */
class ProductPackaging
{
    

    
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * 
     * @ORM\ManyToOne(targetEntity="PackagingType", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="type_id", referencedColumnName="type_id", onDelete="CASCADE", nullable=false)
     */
    private $type_id;
    
    
    /**
     * 
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", onDelete="CASCADE", nullable=false)
     */
    private $product_id;

    
    /**
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=false, options={"comment"="Product unit quantity in the packaging"})
     */
    private $quantity;
    
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
     * @ORM\Column(type="string", length=13, nullable=true, options={"comment"="EAN13 barcode"})
     */
    private $barcode_ean13;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"comment"="UPCA barcode"})
     */
    private $barcode_upca;
    
    
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
     * 
     * @param float|int $quantity
     */
    public function setQuantity($quantity) {
        $this->quantity = $quantity;
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
     * @param string $legacy_synchro_at
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
        return $this->getAvailableStock();
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
    
    
}