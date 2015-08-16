<?php
/**
 * Created by A.Belyakovskiy.
 * Date: 8/16/15
 * Time: 2:21 PM
 */

namespace OrientDbBundle;


use Doctrine\OrientDB\Query\Query;
use PhpOrient\Protocols\Binary\Data\ID;
use PhpOrient\Protocols\Binary\Data\Record;

/**
 * Class OrientDbRepository
 * @package OrientDbBundle
 */
class OrientDbRepository
{
    /**
     * @var OrientDbEntityManager
     */
    protected $em;

    /**
     * @var string
     */
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
        $sql = (new Query())->from([$this->dbClass])
            ->limit($limit)
            ->getRaw();

        $data = $this->prepareClient()
            ->query($sql);

        return $this->populate($data);
    }

    /**
     * @param string|ID $rid
     * @return OrientDbEntityInterface|null
     */
    public function findByRid($rid)
    {
        $sql = (new Query())->from([$this->dbClass])
            ->where('@rid=?', $rid)
            ->getRaw();

        $data = $this->prepareClient()
            ->query($sql);

        return $this->populate($data);
    }

    /**
     * @param $condition
     * @param $params
     * @return OrientDbEntityInterface
     */
    public function find($condition, $params)
    {
        $sql = (new Query())->from([$this->dbClass])
            ->where($condition, $params)
            ->limit(1)
            ->getRaw();

        $data = $this->prepareClient()->query($sql);
        $collection = $this->populate($data);

        return array_pop($collection);
    }

    /**
     * @param $object
     * @return bool|\stdClass
     */
    public function persist(OrientDbEntityInterface $object)
    {
        $reflectionClass = new \ReflectionClass($object);

        $params = [];
        foreach($reflectionClass->getProperties() as $property) {
            /* @var $property \ReflectionProperty */
            if($property->isPublic()) {
                $params[$property->name] = $object->{$property->name};
            }
        }

        if(empty($object->getRid())) {
            $object->setRid(
                $this->insert($params)
            );
        }
    }

    /**
     * @param $params
     * @return OrientDbEntityInterface
     * @throws \Exception
     */
    public function insert($params)
    {
        $sql = (new Query())->insert()
            ->into($this->dbClass)
            ->fields(array_keys($params))
            ->values(array_values($params))
            ->getRaw();

        $record = $this->prepareClient()->command($sql);/* @var $record Record */

        return $record->getRid();
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
        foreach ($objects as $object) {
            $entities[] = $this->populateRecord($object);
        }

        return $entities;
    }

    /**
     * @param $record
     * @return \stdClass
     * @throws \Exception
     */
    protected function populateRecord(Record $record)
    {
        $classMap = $this->em->classMap;
        $key = $record->getOClass();
        if (array_key_exists($key, $classMap)) {

            $entity = new $classMap[$key]; /* @var OrientDbEntityInterface $entity */
            $entity->setRid($record->getRid());

            $oData = $record->getOData();
            if (!empty($oData) && is_array($oData)) {
                foreach ($oData as $key => $value) {
                    $entity->{$key} = $value;
                }
            }
            return $entity;
        }

        throw new OrientDbException('Key not found');
    }

}