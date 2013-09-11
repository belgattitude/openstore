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
 *   name="product_unit",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_reference_idx",columns={"reference"}),
 *     @ORM\UniqueConstraint(name="unique_legacy_mapping_idx",columns={"legacy_mapping"}),
 *   }, 
 *   indexes={
 *     @ORM\Index(name="title_idx", columns={"title"}),
 *   },
 *   options={"comment" = "Product unit table"}
 * )
 */
class ProductUnit implements InputFilterAwareInterface
{
	
	/**
	 * @var \Zend\InputFilter\InputFilterInterface $inputFilter
	 */
	protected $inputFilter;

    /**
     * @ORM\OneToMany(targetEntity="ProductBrandTranslation", mappedBy="brand_id")
     **/
    private $translations;	
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="unit_id", type="integer", nullable=false, options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $unit_id;

	/**
	 * @ORM\Column(type="string", length=10, nullable=false, options={"comment" = "Universal reference, kg, m3..."})
	 */
	private $reference;


	/**
	 * @ORM\Column(type="string", length=80, nullable=true)
	 */
	private $title;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $description;

	
	/**
	 * @ORM\Column(type="boolean", nullable=false, options={"default"=1, "comment"="Whether the unit is active"})
	 */
	private $flag_active;	
	
	
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
		
		 $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
		 /**
		  * Default value for flag_active
		  */
		 $this->flag_active = true; 
		 
	}

	/**
	 * 
	 * @param integer $unit_id
	 */
	public function setUnitId($unit_id)
	{
		$this->unit_id = $unit_id;
		return $this;
	}	
	
	/**
	 * 
	 * @return integer
	 */
	public function getUnitId()
	{
		return $this->unit_id;
	}

	/**
	 * Set reference
	 * @param string $reference
	 */
	public function setReference($reference)
	{
		$this->reference = $reference;
		return $this;
	}

	/**
	 * Return reference 
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
	
	/**
	 * 
	 * @return boolean
	 */
	public function getFlagActive()
	{
		return (boolean) $this->flag_active;
	}

	
	/**
	 * 
	 */
	public function setFlagActive($flag_active)
	{
		$this->flag_active = $flag_active;
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