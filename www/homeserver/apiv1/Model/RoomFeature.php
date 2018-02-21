<?php

namespace homeserver\apiv1\Model;


use homeserver\apiv1\Repository\FeatureRepository;
use homeserver\apiv1\Repository\RoomRepository;

class RoomFeature extends BaseModel
{
    /**
     * @var int id of the room, to which the feature belongs to
     */
    private $roomId;

    /**
     * @var int id of the feature which belongs to the room
     */
    private $featureId;

    /**
     * @Inject
     * @var RoomRepository the repository to get the room
     */
    private $roomRepository;

    /**
     * @Inject
     * @var FeatureRepository the repository to get the feature
     */
    private $featureRepository;

    /**
     * @return int id of the room, to which the feature belongs to
     */
    public function getRoomId()
    {
        return $this->roomId;
    }

    /**
     * @param int $roomId id of the room, to which the feature belongs to
     */
    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;
    }


    /**
     * @return Room the room, to which the feature belongs to
     * @throws \Exception if the room could not be fetched
     */
    public function getRoom()
    {
        return $this->roomRepository->getById($this->roomId);
    }

    /**
     * @param Room $room the room, to which the feature belongs to
     */
    public function setRoom($room)
    {
        $this->roomId = $room == null ? 0 : $room->getId();
    }

    /**
     * @return int id of the feature
     */
    public function getFeatureId()
    {
        return $this->featureId;
    }

    /**
     * @param int $featureId id of the feature
     */
    public function setFeatureId($featureId)
    {
        $this->featureId = $featureId;
    }

    /**
     * @return Feature the feature which belongs to the room
     * @throws \Exception if the feature could not be fetched
     */
    public function getFeature()
    {
        return $this->featureRepository->getById($this->featureId);
    }

    /**
     * @param Feature $feature the feature which belongs to the room
     */
    public function setFeature(Feature $feature)
    {
        $this->featureId = $feature == null ? 0 : $feature->getId();
    }

    public function jsonSerialize()
    {
        $baseArray = parent::jsonSerialize();
        $baseArray['roomId'] = $this->roomId;
        $baseArray['featureId'] = $this->featureId;
        return $baseArray;
    }

}
