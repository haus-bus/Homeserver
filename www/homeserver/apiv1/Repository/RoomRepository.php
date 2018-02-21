<?php

namespace homeserver\apiv1\Repository;

use homeserver\apiv1\Model\Room;

class RoomRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.room")
     * @var string name of the db table
     */
    protected $tableName;


    /**
     * @param array $row the fetched row from the database
     * @return Room the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        $room = $this->factory->make(Room::class, [
            'id' => intval($row['id']),
            'name' => $row['name']
        ]);
        $room->setLastModified(new \DateTime($row['lastChange'], new \DateTimeZone("Europe/Berlin")));
        $room->setPictureName($row['picture']);
        $room->setCreated(null);
        return $room;
    }
}
