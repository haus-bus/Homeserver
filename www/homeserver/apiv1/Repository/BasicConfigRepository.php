<?php

namespace homeserver\apiv1\Repository;


use homeserver\apiv1\Model\BasicConfig;

class BasicConfigRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.basicConfig")
     * @var string name of the db table
     */
    protected $tableName;


    /**
     * @param array $row the fetched row from the database
     * @return BasicConfig the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        $instance = $this->factory->make(BasicConfig::class, [
            'id' => intval($row['id']),
            'name' => $row['paramKey']
        ]);
        $instance->setValue($row['paramValue']);
        $instance->setCreated(null);
        return $instance;
    }
}
