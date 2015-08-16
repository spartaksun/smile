<?php
/**
 * Created by A.Belyakovskiy.
 * Date: 8/16/15
 * Time: 3:16 PM
 */

namespace LocationBundle\Entity;


use OrientDbBundle\OrientDbEntity;

/**
 * Country entity
 * @package LocationBundle\Entity
 */
class Country extends OrientDbEntity
{
    public $name;
}