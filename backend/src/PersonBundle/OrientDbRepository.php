<?php

namespace PersonBundle;


use Doctrine\OrientDB\Query\Command\Select;
use PhpOrient\Protocols\Binary\Data\Record;

class OrientDbRepository
{
    protected $em;
    protected $dbClass;

    /**
     * @param $dbClass string
     * @param OrientDbEntityManager $em
     */
    function __construct($dbClass, OrientDbEntityManager $em)
    {
        $this->em = $em;
        $this->dbClass = $dbClass;
    }

    /**
     * @param int $limit
     * @return null
     */
    public function findAll($limit = 20)
    {
        $sql = (new Select())->from([$this->dbClass])
            ->limit($limit)
            ->getRaw();

        $data = $this->prepareClient()
            ->query($sql);

        return $this->populate($data);
    }

    /**
     * @return \PhpOrient\PhpOrient
     */
    protected function prepareClient()
    {
        $client = $this->em->getClient();
        $client->connect();
        $client->dbOpen($this->em->getDbName());

        return $client;
    }

    /**
     * @param $objects
     * @return null
     */
    protected function populate(array $objects)
    {
        $entities = [];
        $classMap = $this->em->classMap;

        foreach ($objects as $object) {
            if ($object instanceof Record) {
                $key = $object->getOClass();
                if (array_key_exists($key, $classMap)) {
                    $entity = new $classMap[$key];
                    $oData = $object->getOData();
                    if (!empty($oData) && is_array($oData)) {
                        foreach ($oData as $key => $value) {
                            $entity->{$key} = $value;
                        }
                    }
                    $entities[] = $entity;
                }
            }
        }

        return $entities;
    }
}