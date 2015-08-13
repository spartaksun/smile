<?php

namespace PersonBundle;


use PhpOrient\PhpOrient;

class OrientDbEntityManager
{

    /**
     * @var PhpOrient
     */
    protected $client;

    /**
     * @var string
     */
    protected $dbName;

    /**
     * @var array class mapping
     */
    public $classMap = [];

    /**
     * @param PhpOrient $client
     */
    public function __construct(PhpOrient $client, $dbName)
    {
        $this->client = $client;
        $this->dbName = $dbName;
    }

    /**
     * @param string $orientClassName
     * @return OrientDbRepository
     */
    public function getRepository($orientClassName)
    {
        return new OrientDbRepository($this->classMap[$orientClassName], $this);
    }

    public function shortClassName($className)
    {
        $exploded = explode("\\", $className);
        return end($exploded);
    }

    /**
     * @return PhpOrient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getDbName()
    {
        return $this->dbName;
    }

}