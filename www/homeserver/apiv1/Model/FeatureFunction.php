<?php
/**
 * A function interface that is supported by a feature class
 */

namespace homeserver\apiv1\Model;


use homeserver\apiv1\Model\Parameters\FeatureFunctionBitmask;
use homeserver\apiv1\Model\Parameters\FeatureFunctionEnum;
use homeserver\apiv1\Model\Parameters\FeatureFunctionParam;
use homeserver\apiv1\Repository\FeatureClassRepository;
use homeserver\apiv1\Repository\FeatureFunctionBitmaskRepository;
use homeserver\apiv1\Repository\FeatureFunctionEnumRepository;
use homeserver\apiv1\Repository\FeatureFunctionParamRepository;

class FeatureFunction extends BaseModel
{
    /**
     * @var int $featureClassesId ID of the feature class, which this function belongs to
     */
    private $featureClassesId;

    /**
     * @var string $type the type of the function, i.e. ACTION, EVENT, ...
     */
    private $type;

    /**
     * @var int $functionId firmware ID of this function
     */
    private $functionId;

    /**
     * @var string $view the view, in which the object will be shown
     */
    private $view;


    /**
     * @Inject
     * @var FeatureClassRepository $featureClassesRepository repo to resolve the feature class
     */
    private $featureClassesRepository;

    /**
     * @Inject
     * @var FeatureFunctionParamRepository $paramRepository repo to resolve the parameters
     */
    private $paramRepository;

    /**
     * @Inject
     * @var FeatureFunctionEnumRepository $enumRepository repo to resolve the enums
     */
    private $enumRepository;

    /**
     * @Inject
     * @var FeatureFunctionBitmaskRepository $bitmaskRepository repo to resolve the bitmasks
     */
    private $bitmaskRepository;


    /**
     * @return int ID of the feature class, which this function belongs to
     */
    public function getFeatureClassesId()
    {
        return $this->featureClassesId;
    }

    /**
     * @param int $featureClassesId ID of the feature class, which this function belongs to
     */
    public function setFeatureClassesId($featureClassesId)
    {
        $this->featureClassesId = $featureClassesId;
    }

    /**
     * @return FeatureClass the feature class, which this function belongs to
     * @throws \Exception if the feature class could not be resolved
     */
    public function getFeatureClass() {
        return $this->featureClassesRepository->getById($this->featureClassesId);
    }

    /**
     * @param FeatureClass $featureClass the feature class, which this function belongs to
     */
    public function setFeatureClass(FeatureClass $featureClass) {
        $this->featureClassesId = $featureClass == null ? 0 : $featureClass->getId();
    }

    /**
     * @return string the type of the function, i.e. ACTION, EVENT, ...
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type the type of the function, i.e. ACTION, EVENT, ...
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int firmware ID of this function
     */
    public function getFunctionId()
    {
        return $this->functionId;
    }

    /**
     * @param int $functionId firmware ID of this function
     */
    public function setFunctionId($functionId)
    {
        $this->functionId = $functionId;
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
     * @return FeatureFunctionParam[] parameters of this function
     * @throws \Exception if the parameters could not be resolved
     */
    public function getParameters(){
        return $this->paramRepository->query(array("featureFunctionId" => "= " . $this->id));
    }

    /**
     * @return FeatureFunctionEnum[] enums used in this function
     * @throws \Exception if the enums could not be resolved
     */
    public function getEnums(){
        return $this->enumRepository->query(array("featureFunctionId" => "= " . $this->id));
    }

    /**
     * @return FeatureFunctionBitmask[] bitmasks used in this function
     * @throws \Exception if the bitmasks could not be resolved
     */
    public function getBitmasks(){
        return $this->bitmaskRepository->query(array("featureFunctionId" => "= " . $this->id));
    }

    /**
     * Converts the given parameters to bytes
     * @param array $paramData a key => value array with the parameter data. The key must be the parameter name
     * @return array a 2-dimensional array which contains the converted bytes for each parameter
     * @throws \Exception if the featureFunctionParameters could not be loaded
     */
    public function convertParams($paramData) {
        $parameters = $this->getParameters();

        // order by paramId
        usort($parameters, function (FeatureFunctionParam $a, FeatureFunctionParam $b) {
            if ($a->getId() == $b->getId()) {
                return 0;
            }
            return $a->getId() < $b->getId() ? -1 : 1;
        });

        $convertedParamValues = array();
        foreach ($parameters as $p) {
            array_push($convertedParamValues,
                array_key_exists($p->getName(), $paramData) ?
                    $p->getBytes($paramData[$p->getName()]) :
                    $p->getBytes(null));
        }

        return $convertedParamValues;
    }


    public function jsonSerialize()
    {
        $base = parent::jsonSerialize();
        $base['featureClassesId'] = $this->featureClassesId;
        $base['type'] = $this->type;
        $base['functionId'] = $this->functionId;
        $base['view'] = $this->view;
        return $base;
    }

}
