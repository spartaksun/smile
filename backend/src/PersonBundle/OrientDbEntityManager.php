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

    protected $classMap = [];

    /**
     * @param PhpOrient $client
     */
    public function __construct(PhpOrient $client, $dbName)
    {
        $this->client = $client;
        $this->dbName = $dbName;
    }

    public function classMap()
    {
        return $this->classMap;
    }

    /**
     * @param string $orientClassName
     * @return OrientDbRepository
     */
    public function getRepository($orientClassName)
    {
        $shortClassName = $this->shortClassName($orientClassName);
        $this->classMap[$shortClassName] = $orientClassName;

        return new OrientDbRepository($shortClassName, $this);
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