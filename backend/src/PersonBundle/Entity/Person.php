<?php

namespace PersonBundle\Entity;

use Doctrine\Common\Annotations\Annotation as ORM;

/**
 * Category
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Person
{
    /**
     * @ORM\Attribute(name="@rid",required="true")
     */
    protected $rid;



}
