<?php

namespace homeserver\apiv1\Repository;

use homeserver\apiv1\Model\BaseModel;
use homeserver\apiv1\Model\Test;
use homeserver\apiv1\Utilities\Convert;

class TestRepository extends BaseRepository
{


    /**
     * @Inject("tablenames.test")
     * @var string name of the db table
     */
    protected $tableName;

    /**
     * @param array $row the fetched row from the database
     * @return BaseModel the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        /**
         * @var Test $obj
         */
        $obj = $this->factory->make(Test::class, [
            'id' => intval($row['id']),
            'name' => $row['name'],
            'description' => $row['description'],
        ]);
        $obj->setNumber(intval($row['num']));
        $obj->setTime(Convert::parseTime($row['time']));
        $obj->setCreated(Convert::parseTime($row['created']));
        $obj->setLastModified(Convert::parseTime($row['lastModified']));
        $obj->setModifiedBy($row['modifiedBy']);
        return $obj;
    }
}
