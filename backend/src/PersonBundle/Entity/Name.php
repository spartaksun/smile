<?php

namespace PersonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ODM\Document(class="Name")
 */
class Name
{

    /**
     * @ODM\Property(name='first_ru', type="integer")
     */
    protected $firstRu;

    /**
     * @return mixed
     */
    public function getFirstRu()
    {
        return $this->firstRu;
    }

    /**
     * @param mixed $firstRu
     */
    public function setFirstRu($firstRu)
    {
        $this->firstRu = $firstRu;
    }
}
