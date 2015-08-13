<?php

namespace PersonBundle;


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
     * @return null
     */
    public function findAll()
    {
        $client = $this->em->getClient();
        $client->connect();
        $client->dbOpen($this->em->getDbName());

        $className = $this->dbClass;
        $data = $client->command("SELECT * FROM {$className}");

        return $this->populate($data);
    }

    /**
     * @param $data
     * @return null
     */
    protected function populate($data)
    {
        $object = null;
        $classMap = array_flip($this->em->classMap);

        if ($data instanceof Record) {
            $key = $data->getOClass();
            if (array_key_exists($key, $classMap)) {
                $object = new $classMap[$key];
                $oData = $data->getOData();
                if (!empty($oData) && is_array($oData)) {
                    foreach ($oData as $key => $value) {
                        $object->{$key} = $value;
                    }
                }
            }
        }

        return $object;
    }
}