<?php

namespace homeserver\apiv1\Model;


use homeserver\apiv1\Repository\FeatureRepository;

class LastReceived extends BaseModel
{
    /**
     * @var string $type the type, i.e. RESULT, EVENT, ...
     */
    private $type;

    /**
     * @var string $function the function, to which the data belongs to
     */
    private $function;

    /**
     * @var object $data the transmitted data
     */
    private $data;

    /**
     * @var int $senderObjectId the ID of the feature, which sent the data
     */
    private $senderObjectId;


    /**
     * @Inject
     * @var FeatureRepository $featureRepository the repo to get the sender feature
     */
    private $featureRepository;



    /**
     * @return string the type, i.e. RESULT, EVENT, ...
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type the type, i.e. RESULT, EVENT, ...
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string the function, to which the data belongs to
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @param string $function the function, to which the data belongs to
     */
    public function setFunction($function)
    {
        $this->function = $function;
    }

    /**
     * @return object the transmitted data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param object $data the transmitted data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return int the ID of the feature, which sent the data
     */
    public function getSenderObjectId()
    {
        return $this->senderObjectId;
    }

    /**
     * @param int $senderObjectId the ID of the feature, which sent the data
     */
    public function setSenderObjectId($senderObjectId)
    {
        $this->senderObjectId = $senderObjectId;
    }

    /**
     * @return Feature the sender of this data
     * @throws \Exception if the feature could not be loaded
     */
    public function getSender() {
        $result = $this->featureRepository->query(array("objectId" => "= " . $this->senderObjectId));
        return count($result) >= 1 ? $result[0] : null;
    }

    /**
     * @param Feature $sender the feature which has sent the data
     */
    public function setSender(Feature $sender) {
        $this->senderObjectId = $sender == null ? 0 : $sender->getObjectId();
    }


    public function jsonSerialize()
    {
        $base = parent::jsonSerialize();
        $base['type'] = $this->type;
        $base['function'] = $this->function;
        $base['data'] = $this->data;
        $base['senderObjectId'] = $this->senderObjectId;
        return $base;
    }


}
