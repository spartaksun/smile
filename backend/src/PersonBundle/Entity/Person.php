<?php

namespace PersonBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\OrientDB\Mapper\Annotations as ODM;

/**
 * @ODM\Document(class="Person")
 */
class Person
{
    /**
     * @ODM\Property(name="@rid", type="string")
     */
    protected $rid;


    /**
     * @ODM\Property(type="integer")
     */
    protected $id;

    /**
     * @var ArrayCollection
     * @ODM\OneToMany(targetEntity="PersonBundle\Entity\Name", mappedBy="parent")
     */
    public $name;


    public function setId($id)
    {
        $this->id = $id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

}
