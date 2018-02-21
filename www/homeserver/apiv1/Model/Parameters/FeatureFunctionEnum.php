<?php

namespace homeserver\apiv1\Model\Parameters;


use homeserver\apiv1\Model\BaseModel;
use homeserver\apiv1\Model\FeatureFunction;
use homeserver\apiv1\Repository\FeatureFunctionParamRepository;
use homeserver\apiv1\Repository\FeatureFunctionRepository;

class FeatureFunctionEnum extends BaseModel
{

    /**
     * @var int $featureFunctionId ID of the feature function, which this enum belongs to
     */
    private $featureFunctionId;

    /**
     * @var int $paramId id of the parameter, which this eunm belongs to
     */
    private $paramId;

    /**
     * @var int $value the enum value
     */
    private $value;


    /**
     * @Inject
     * @var FeatureFunctionRepository $featureFunctionRepository repo to resolve the feature function
     */
    private $featureFunctionRepository;

    /**
     * @Inject
     * @var FeatureFunctionParamRepository $featureFunctionParamRepository repo to resolve the feature function parameter
     */
    private $featureFunctionParamRepository;



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
     * @return int id of the parameter, which this eunm belongs to
     */
    public function getParamId()
    {
        return $this->paramId;
    }

    /**
     * @param int $paramId id of the parameter, which this eunm belongs to
     */
    public function setParamId($paramId)
    {
        $this->paramId = $paramId;
    }

    /**
     * @return FeatureFunctionParam the feature function parameter, which this enum belongs to
     * @throws \Exception if the feature function param could not be resolved
     */
    public function getFeatureFunctionParam() {
        return $this->featureFunctionParamRepository->getById($this->paramId);
    }

    /**
     * @param FeatureFunctionParam $featureFunctionParam the feature function param, which this enum belongs to
     */
    public function setFeatureFunctionParam(FeatureFunctionParam $featureFunctionParam) {
        $this->paramId = $featureFunctionParam == null ? 0 : $featureFunctionParam->getId();
    }

    /**
     * @return int the enum value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value the enum value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }




    public function jsonSerialize()
    {
        $base = parent::jsonSerialize();
        $base['featureFunctionId'] = $this->featureFunctionId;
        $base['paramId'] = $this->paramId;
        $base['value'] = $this->value;
        return $base;
    }

}
