<?php

namespace homeserver\apiv1\Model;


use homeserver\apiv1\Repository\FeatureRepository;

class Room extends BaseModel
{
    public static $picsDirectory = "homeserver/userpics/";

    /**
     * @Inject
     * @var FeatureRepository the repo to get the features of this room
     */
    private $featureRepository;

    /**
     * @var string $pictureName The picture name
     */
    private $pictureName;

    /**
     * @return string
     */
    public function getPictureName()
    {
        return $this->pictureName;
    }

    /**
     * @param string $pictureName
     */
    public function setPictureName($pictureName)
    {
        $this->pictureName = $pictureName;
    }

    /**
     * @return null|string the path to the picture
     */
    public function getPicturePath()
    {
        if ($this->pictureName != null && strlen($this->pictureName) > 0)
            return Room::$picsDirectory . $this->pictureName;
        else return null;
    }

    public function jsonSerialize()
    {
        $baseArray = parent::jsonSerialize();
        $baseArray['picture'] = $this->getPicturePath();
        return $baseArray;
    }


    /**
     * @return Feature[] all features that belong to this room
     * @throws \Exception if the features could not be fetched
     */
    public function getFeatures()
    {
        return $this->featureRepository->getByRoom($this);
    }
}
