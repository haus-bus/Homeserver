<?php

namespace homeserver\apiv1\Repository;

use homeserver\apiv1\Model\Room;
use homeserver\apiv1\Model\RoomFeature;

class RoomFeatureRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.roomFeature")
     * @var string name of the db table
     */
    protected $tableName;


    /**
     * @param array $row the fetched row from the database
     * @return RoomFeature the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        $roomFeature = $this->factory->make(RoomFeature::class, [
            'id' => intval($row['id'])
        ]);
        $roomFeature->setRoomId(intval($row['roomId']));
        $roomFeature->setFeatureId(intval($row['featureInstanceId']));
        $roomFeature->setLastModified(new \DateTime($row['lastChange'], new \DateTimeZone("Europe/Berlin")));
        $roomFeature->setCreated(null);
        return $roomFeature;
    }
}
