<?php
/**
 * Created by A.Belyakovskiy.
 * Date: 8/16/15
 * Time: 3:20 PM
 */

namespace OrientDbBundle;


use PhpOrient\Protocols\Binary\Data\ID;

class OrientDbEntity implements OrientDbEntityInterface
{

    /**
     * @var ID
     */
    private $_rid;

    /**
     * @return ID
     */
    final public function getRid()
    {
        return $this->_rid;
    }

    /**
     * @param ID|string $rid
     */
    final public function setRid($rid)
    {
        if ($rid instanceof ID) {
            $this->_rid = $rid;
        } else {
            if (!preg_match_all("~^\#(\d+):(\d+)$~", $rid, $matches)) {
                throw new OrientDbException('Incorrect @rid string.');
            }

            $id = new ID();
            $id->cluster = $matches[1];
            $id->position = $matches[2];

            $this->_rid = $id;
        }
    }
}
