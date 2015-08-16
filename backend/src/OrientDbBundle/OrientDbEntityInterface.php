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
}

