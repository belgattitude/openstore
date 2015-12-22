<?php

namespace OpenstoreSchema\Core\Entity\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository implements InputFilterAwareInterface
{
    /**
     * @var InputFilterInterface $inputFilter
     */
    protected $inputFilter;


    /**
     * Get all roles hierachically
     */
    public function getAllRoles()
    {
        $roles = array();
        $repo = $this->em->getRepository('OpenstoreSchema\Core\Entity\Role');

        foreach ($user->getRoles() as $role) {
            $roles[] = $role->getName();
            // all parents of this role
            foreach ($repo->getPath($role) as $parent) {
                $roles[] = $parent->getName();
            }
        }
        $roles = array_unique($roles);
        var_dump($roles);
        die();
        //var_dump($user->getRoles()[0]->getName());
    }

    /**
     *
     * @param InputFilterInterface $inputFilter
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFiler = $inputFilter;
        return $this;
    }

    /**
     *
     * @return InputFilterInterface $inputFilter
     */
    public function getInputFilter()
    {
        $md = $this->getClassMetadata();
        var_dump($md);
        die();

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
