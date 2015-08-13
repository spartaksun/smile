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

    public function findAll()
    {
        $client = $this->em->getClient();
        $client->connect();
        $client->dbOpen($this->em->getDbName());

        $className = $this->dbClass;
        $data = $client->command("SELECT * FROM {$className}");

        return $this->populate($data);
    }

    protected function populate($data)
    {
        $classMap = $this->em->classMap();
        $object = new $classMap[$this->dbClass]; // TODO костыль - могут вернуться объекты разных типов

        if ($data instanceof Record) {
            $oData = $data->getOData();
            if (!empty($oData) && is_array($oData)) {
                foreach ($oData as $key => $value) {
                    $object->{$key} = $value;
                }
            }
        }

        return $object;
    }


}