<?php
/**
 * Created by A.Belyakovskiy.
 * Date: 8/16/15
 * Time: 2:21 PM
 */

namespace OrientDbBundle;

use PhpOrient\Protocols\Binary\Data\ID;

/**
 * Class OrientDbEntityInterface
 * @package OrientDbBundle
 */
interface OrientDbEntityInterface
{
    /**
     * @return ID
     */
    public function getRid();

    /**
     * @param $rid
     * @return void
     */
    public function setRid($rid);

    /**
     * Value of attribute
     * @param $attributeName
     * @return false|OrientDbEntity|OrientDbEntity[]
     */
    public function getAttribute($attributeName);

    /**
     * Entity attributes
     * @return array of name => value pairs
     */
    public function getAttributes();

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes);

    /**
     * @param $name
     * @param $value
     */
    public function setAttribute($name, $value);

    /**
     * @return array of callable validators
     */
    public function validators();

    /**
     * @return array
     */
    public function getErrors();

    /**
     * @param $attribute
     * @param $error
     * @return mixed
     */
    public function addError($attribute, $error);

}

