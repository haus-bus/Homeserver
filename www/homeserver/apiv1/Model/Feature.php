<?php

namespace homeserver\apiv1\Model;

use homeserver\apiv1\Repository\FeatureClassRepository;
use homeserver\apiv1\Repository\FeatureRepository;
use homeserver\apiv1\Repository\LastReceivedRepository;
use homeserver\apiv1\Utilities\ICommunicator;

class Feature extends BaseModel
{

    /**
     * @var int $objectId Firmware-ID of the instance
     */
    private $objectId;

    /**
     * @var int $parentId DB-Id of the parent object
     */
    private $parentId;

    /**
     * @var int DB-ID of the FeatureClass
     */
    private $featureClassesId;

    /**
     * @var int DB-ID of the controller, to which this feature belongs to
     */
    private $controllerId;

    /**
     * @var string question: what is this property used for?
     */
    private $port;

    /**
     * @var bool object is ok
     */
    private $checked;

    /**
     * @Inject
     * @var FeatureRepository the repository to get featureinstances
     */
    private $featureRepository;

    /**
     * @Inject
     * @var FeatureClassRepository $featureClassRepository
     */
    private $featureClassRepository;

    /**
     * @Inject
     * @var LastReceivedRepository $lastReceivedRepository
     */
    private $lastReceivedRepository;

    // todo: controllerRepository

    /**
     * @Inject
     * @var ICommunicator $communicator the communicator to send messages
     */
    private $communicator;

    /**
     * @Inject("functionTypes")
     * @var array $funcTypes holds the possible function types
     */
    private static $funcTypes;


    /**
     * @return int Firmware-ID of the instance
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId Firmware-ID of the instance
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
    }

    /**
     * @return int DB-Id of the parent object
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId DB-Id of the parent object
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return int DB-ID of the FeatureClass
     */
    public function getFeatureClassesId()
    {
        return $this->featureClassesId;
    }

    /**
     * @param int $featureClassesId DB-ID of the FeatureClass
     */
    public function setFeatureClassesId($featureClassesId)
    {
        $this->featureClassesId = $featureClassesId;
    }

    /**
     * @return int DB-ID of the controller, to which this feature belongs to
     */
    public function getControllerId()
    {
        return $this->controllerId;
    }

    /**
     * @param int $controllerId DB-ID of the controller, to which this feature belongs to
     */
    public function setControllerId($controllerId)
    {
        $this->controllerId = $controllerId;
    }

    /**
     * @return string question: what is this property used for?
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $port question: what is this property used for?
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return bool object is ok
     */
    public function isChecked()
    {
        return $this->checked;
    }

    /**
     * @param bool $checked object is ok
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;
    }

    /**
     * @return Feature get the parent feature instance
     * @throws \Exception if the feature could not be fetched
     */
    public function getParent()
    {
        return $this->featureRepository->getById($this->parentId);
    }

    /**
     * @param Feature $parent set the parent feature instance
     */
    public function setParent(Feature $parent)
    {
        $this->parentId = $parent == null ? 0 : $parent->getId();
    }

    /**
     * @return FeatureClass get the feature class
     * @throws \Exception if the feature class could not be fetched
     */
    public function getFeatureClass()
    {
        return $this->featureClassRepository->getById($this->featureClassesId);
    }

    /**
     * @param FeatureClass $featureClass the feature class of this object
     */
    public function setFeatureClass(FeatureClass $featureClass)
    {
        $this->featureClassesId = $featureClass == null ? 0 : $featureClass->getId();
    }

    /**
     * @param $type string the function name or the type (EVENT, RESULT, ...) for which the results should be retrieved
     * @return LastReceived[] the latest received data
     * @throws \Exception if the data could not be fetched
     */
    public function getLastReceived($type = null)
    {
        $constraints = array("senderObj" => "= " . $this->objectId);
        if (strlen($type) > 0) {
            if (in_array(strtoupper($type), self::$funcTypes)) {
                $constraints["type"] = "= " . strtoupper($type);
            }
            else {
                $constraints["function"] = "= " . $type;
            }
        }

        return $this->lastReceivedRepository->query($constraints);
    }

    /**
     * Executes a method on this feature instance
     *
     * @param string|int|FeatureFunction $function the function to execute
     * @param array $params a key => value array with the parameter data. The key must be the parameter name
     * @param int $senderObjectId the sender of the method call
     * @return bool todo: return result
     * @throws \Exception if something goes wrong...
     */
    public function execInstanceMethod($function, $params = array(), $senderObjectId = 0)
    {
        /* @var \homeserver\apiv1\Model\FeatureFunction $selectedFunction */
        $selectedFunction = null;

        if (is_object($function)
            && get_class($function) == FeatureFunction::class
            && $function->getFeatureClassesId() == $this->featureClassesId) {

            // $function is already the right object
            $selectedFunction = $function;
        } else {
            // resolve the function object by id or by name
            $class = $this->getFeatureClass();
            if ($class == null) {
                throw new \Exception("could not load feature class with id " . $this->featureClassesId);
            }
            $possibleFunctions = $class->getFeatureFunctions();
            foreach ($possibleFunctions as $func) {
                if ($func->getId() === $function || $func->getName() === $function) {
                    $selectedFunction = $func;
                    break;
                }
            }
        }

        // could the function object be resolved?
        if ($selectedFunction == null) {
            throw new \Exception("The function '$function' does not exist in feature class " . $this->featureClassesId);
        }

        // create the data to send consisting of the function id and the parameter data
        $data[] = array($selectedFunction->getFunctionId());
        foreach ($selectedFunction->convertParams($params) as $parData) {
            $data[] = $parData;
        }

        // send data
        $this->communicator->sendData($this->objectId, $data, $senderObjectId);

        // todo: receive result
        return true;
    }

    public function jsonSerialize()
    {
        $baseArray = parent::jsonSerialize();
        $baseArray['objectId'] = $this->objectId;
        $baseArray['parentId'] = $this->parentId;
        $baseArray['featureClassesId'] = $this->featureClassesId;
        $baseArray['controllerId'] = $this->controllerId;
        $baseArray['port'] = $this->port;
        $baseArray['checked'] = $this->checked;
        return $baseArray;
    }

}
