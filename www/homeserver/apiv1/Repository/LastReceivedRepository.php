<?php

namespace homeserver\apiv1\Repository;


use homeserver\apiv1\Model\LastReceived;

class LastReceivedRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.lastReceived")
     * @var string name of the db table
     */
    protected $tableName;


    /**
     * @param array $row the fetched row from the database
     * @return LastReceived the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        $room = $this->factory->make(LastReceived::class, [
            'id' => intval($row['id']),
        ]);
        $created = new \DateTime();
        $created->setTimestamp($row['time']);
        $room->setCreated($created);
        $room->setType($row['type']);
        $room->setFunction($row['function']);
        $room->setData(unserialize($row['functionData']));
        $room->setSenderObjectId(intval($row['senderObj']));
        return $room;
    }
}
