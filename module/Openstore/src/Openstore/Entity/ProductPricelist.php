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
 *   name="product_pricelist",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_product_price_idx",columns={"product_id", "pricelist_id"}),
 *     @ORM\UniqueConstraint(name="unique_legacy_mapping_idx",columns={"legacy_mapping"}),
 *   }, 
 *   indexes={
 *     @ORM\Index(name="price_idx", columns={"price"}),
 *   },
 *   options={"comment" = "Product pricelist"}
 * )
 */
class ProductPricelist implements InputFilterAwareInterface
{
	
	/**
	 * @var \Zend\InputFilter\InputFilterInterface $inputFilter
	 */
	protected $inputFilter;

	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	
	
	/**
	 * 
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", onDelete="CASCADE", nullable=false)
	 */
	private $product_id;

	/**
	 * 
     * @ORM\ManyToOne(targetEntity="Pricelist", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="pricelist_id", referencedColumnName="pricelist_id", onDelete="CASCADE", nullable=false)
	 */
	private $pricelist_id;
	
	
	/**
	 * @ORM\Column(type="decimal", precision=12, scale=6, nullable=false, options={"comment"="Unit sales price"})
	 */
	private $price;
	
	
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
	 * @return float
	 */
	public function getPrice()
	{
		return (boolean) $this->price;
	}

	
	/**
	 * 
	 */
	public function setPrice($price)
	{
		$this->price = $price;
		return $this;
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
	 * 
	 * @return string
	 */
	public function getDeletedAt()
	{
		return $this->deleted_at;
	}

	/**
	 * 
	 * @param string $updated_at
	 */
	public function setDeletedAt($deleted_at)
	{
		$this->deleted_at = $deleted_at;
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
		return $this->getPrice();
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