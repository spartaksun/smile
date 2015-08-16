<?php
/**
 * Created by A.Belyakovskiy.
 * Date: 8/16/15
 * Time: 3:16 PM
 */

namespace LocationBundle\Entity;


use spartaksun\OrientDb\Entity;
use spartaksun\OrientDb\Validators\StringValidator;

/**
 * Country entity
 * @package LocationBundle\Entity
 *
 * @property $name
 */
class Country extends Entity
{
    /**
     * {@inheritdoc}
     */
    public function validators()
    {
        return [
            'name' => [
                [
                    StringValidator::class,
                    [
                        'max' => 10,
                    ],
                ],
            ],
        ];
    }
}