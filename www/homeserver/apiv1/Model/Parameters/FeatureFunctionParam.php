<?php

namespace homeserver\apiv1\Model\Parameters;


use homeserver\apiv1\Model\BaseModel;
use homeserver\apiv1\Model\FeatureFunction;
use homeserver\apiv1\Repository\FeatureFunctionBitmaskRepository;
use homeserver\apiv1\Repository\FeatureFunctionEnumRepository;
use homeserver\apiv1\Repository\FeatureFunctionRepository;

abstract class FeatureFunctionParam extends BaseModel
{
    /**
     * the type of this parameter, i.e. BYTE, BITMASK, WORD, ...
     */
    const TYPE = "UNDEFINED";

    /**
     * @var int $featureFunctionId ID of the feature function, which this parameter belongs to
     */
    private $featureFunctionId;

    /**
     * @var string $view the view, in which the object will be shown
     */
    private $view;


    /**
     * @Inject
     * @var FeatureFunctionRepository $featureFunctionRepository repo to resolve the feature function
     */
    private $featureFunctionRepository;

    /**
     * @Inject
     * @var FeatureFunctionEnumRepository $enumRepository repo to resolve the feature function enums
     */
    private $enumRepository;

    /**
     * @Inject
     * @var FeatureFunctionBitmaskRepository $bitmaskRepository repo to resolve the feature function bitmasks
     */
    private $bitmaskRepository;



    /**
     * @return int ID of the feature function, which this parameter belongs to
     */
    public function getFeatureFunctionId()
    {
        return $this->featureFunctionId;
    }

    /**
     * @param int $featureFunctionId ID of the feature function, which this parameter belongs to
     */
    public function setFeatureFunctionId($featureFunctionId)
    {
        $this->featureFunctionId = $featureFunctionId;
    }

    /**
     * @return FeatureFunction the feature function, which this parameter belongs to
     * @throws \Exception if the feature function could not be resolved
     */
    public function getFeatureFunction() {
        return $this->featureFunctionRepository->getById($this->featureFunctionId);
    }

    /**
     * @param FeatureFunction $featureFunction the feature function, which this parameter belongs to
     */
    public function setFeatureFunction(FeatureFunction $featureFunction) {
        $this->featureFunctionId = $featureFunction == null ? 0 : $featureFunction->getId();
    }

    /**
     * @return string the view, in which the object will be shown
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param string $view the view, in which the object will be shown
     */
    public function setView($view)
    {
        $this->view = $view;
    }


    /**
     * @return FeatureFunctionEnum[] enums of this parameter
     * @throws \Exception if the enums could not be resolved
     */
    public function getEnums(){
        return $this->enumRepository->query(array(
            "featureFunctionId" => "= " . $this->featureFunctionId,
            "paramId" => "= " . $this->id));
    }

    /**
     * @return FeatureFunctionBitmask[] bitmasks of this parameter
     * @throws \Exception if the bitmasks could not be resolved
     */
    public function getBitmasks(){
        return $this->bitmaskRepository->query(array(
            "featureFunctionId" => "= " . $this->featureFunctionId,
            "paramId" => "= " . $this->id));
    }


    public function jsonSerialize()
    {
        $base = parent::jsonSerialize();
        $base['featureFunctionId'] = $this->featureFunctionId;
        $base['type'] = $this::TYPE;
        $base['view'] = $this->view;
        return $base;
    }

    /**
     * @param mixed $paramValue the value to convert to bytes
     * @return array returns an array containing the given value as bytes
     */
    public abstract function getBytes($paramValue);

}
