<?php

namespace homeserver\apiv1\Repository;


use Exception;
use homeserver\apiv1\Model\BaseModel;
use homeserver\apiv1\Model\Feature;
use homeserver\apiv1\Model\Room;

class FeatureRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.feature")
     * @var string name of the db table
     */
    protected $tableName;


    /**
     * @Inject
     * @var RoomFeatureRepository
     */
    protected $roomFeatureRepo;


    /**
     * @param Room|int $room room or roomId of the room, for which the features should be loaded
     * @return Feature[] the features of the given room
     * @throws \Exception if the data source could not be accessed or the instances could not be created
     */
    public function getByRoom($room)
    {
        if (is_int($room)) {
            $roomId = $room;
        } else {
            $roomId = $room == null ? 0 : $room->getId();
        }
        $featureTableName = $this->tableName;
        $roomFeatureTableName = $this->roomFeatureRepo->getTableName();
        $rows = $this->db->query("SELECT * FROM $featureTableName WHERE id IN (SELECT featureInstanceId FROM $roomFeatureTableName WHERE roomId = $roomId)");

        $result = [];
        foreach ($rows as $row) {
            array_push($result, $this->createInstance($row));
        }
        return $result;
    }


    /**
     * @param array $row the fetched row from the database
     * @return Feature the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        $feature = $this->factory->make(Feature::class, [
            'id' => intval($row['id']),
            'name' => $row['name']
        ]);
        $feature->setControllerId(intval($row['controllerId']));
        $feature->setFeatureClassesId(intval($row['featureClassesId']));
        $feature->setObjectId(intval($row['objectId']));
        $feature->setPort(intval($row['port']));
        $feature->setChecked(boolval($row['checked']));
        $feature->setParentId(intval($row['parentInstanceId']));
        $feature->setLastModified(new \DateTime($row['lastChange'], new \DateTimeZone("Europe/Berlin")));
        $feature->setCreated(null);
        return $feature;

    }
}
