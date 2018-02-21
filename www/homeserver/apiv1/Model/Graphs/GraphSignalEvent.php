<?php

namespace homeserver\apiv1\Model\Graphs;


use homeserver\apiv1\Model\BaseModel;
use homeserver\apiv1\Model\Feature;
use homeserver\apiv1\Model\FeatureFunction;
use homeserver\apiv1\Repository\FeatureFunctionRepository;
use homeserver\apiv1\Repository\FeatureRepository;
use homeserver\apiv1\Repository\GraphSignalRepository;


class GraphSignalEvent extends BaseModel
{

    /**
     * @var int $graphSignalsId the id of the graphSignal to which this event belongs to
     */
    private $graphSignalsId;

    /**
     * @var int $featureInstanceId the id of the feature to which this event belongs to
     */
    private $featureInstanceId;

    /**
     * @var int $functionId the id of the function this event is based on
     */
    private $functionId;

    /**
     * @var string $graphValueFunction a user defined function to calculate the graph value
     */
    private $graphValueFunction;


    /**
     * @Inject
     * @var GraphSignalRepository $signalRepo
     */
    private $signalRepo;

    /**
     * @Inject
     * @var FeatureRepository $featureRepo
     */
    private $featureRepo;

    /**
     * @Inject
     * @var FeatureFunctionRepository $functionRepo
     */
    private $functionRepo;



    /**
     * @return int the id of the graphSignal to which this event belongs to
     */
    public function getGraphSignalsId()
    {
        return $this->graphSignalsId;
    }

    /**
     * @param int $graphSignalsId the id of the graphSignal to which this event belongs to
     */
    public function setGraphSignalsId($graphSignalsId)
    {
        $this->graphSignalsId = $graphSignalsId;
    }

    /**
     * @return int the id of the feature to which this event belongs to
     */
    public function getFeatureInstanceId()
    {
        return $this->featureInstanceId;
    }

    /**
     * @param int $featureInstanceId the id of the feature to which this event belongs to
     */
    public function setFeatureInstanceId($featureInstanceId)
    {
        $this->featureInstanceId = $featureInstanceId;
    }

    /**
     * @return int the id of the function this event is based on
     */
    public function getFunctionId()
    {
        return $this->functionId;
    }

    /**
     * @param int $functionId the id of the function this event is based on
     */
    public function setFunctionId($functionId)
    {
        $this->functionId = $functionId;
    }

    /**
     * @return string a user defined function to calculate the graph value
     */
    public function getGraphValueFunction()
    {
        return $this->graphValueFunction;
    }

    /**
     * @param string $graphValueFunction a user defined function to calculate the graph value
     */
    public function setGraphValueFunction($graphValueFunction)
    {
        $this->graphValueFunction = $graphValueFunction;
    }


    /**
     * @return GraphSignal the signal this event belongs to
     * @throws \Exception if database access fails
     */
    public function getSignal() {
        return $this->signalRepo->getById($this->graphSignalsId);
    }

    /**
     * @return Feature the feature this event belongs to
     * @throws \Exception if database access fails
     */
    public function getFeature() {
        return $this->featureRepo->getById($this->featureInstanceId);
    }

    /**
     * @return FeatureFunction the function this event belongs to
     * @throws \Exception if database access fails
     */
    public function getFeatureFunction() {
        return $this->functionRepo->getById($this->functionId);
    }


    public function jsonSerialize()
    {
        $base = parent::jsonSerialize();
        $base['graphSignalsId'] = $this->graphSignalsId;
        $base['featureInstanceId'] = $this->featureInstanceId;
        $base['functionId'] = $this->functionId;
        $base['graphValueFunction'] = $this->graphValueFunction;
        return $base;
    }
}
