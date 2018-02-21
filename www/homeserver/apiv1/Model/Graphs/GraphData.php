<?php

namespace homeserver\apiv1\Model\Graphs;


use homeserver\apiv1\Model\BaseModel;
use homeserver\apiv1\Repository\GraphRepository;
use homeserver\apiv1\Repository\GraphSignalRepository;

class GraphData extends BaseModel
{
    /**
     * @var int $graphId the ID of the graph this value belongs too
     */
    private $graphId;

    /**
     * @var int $signalId the ID of the signal this value belongs to
     */
    private $signalId;

    /**
     * @var float $value the value
     */
    private $value;


    /**
     * @Inject
     * @var GraphSignalRepository $signalRepo to get the signal for this data
     */
    private $signalRepo;

    /**
     * @Inject
     * @var GraphRepository $graphRepo to get the graph this data belongs to
     */
    private $graphRepo;




    /**
     * @return int the ID of the graph this value belongs too
     */
    public function getGraphId()
    {
        return $this->graphId;
    }

    /**
     * @param int $graphId the ID of the graph this value belongs too
     */
    public function setGraphId($graphId)
    {
        $this->graphId = $graphId;
    }

    /**
     * @return int the ID of the signal this value belongs to
     */
    public function getSignalId()
    {
        return $this->signalId;
    }

    /**
     * @param int $signalId the ID of the signal this value belongs to
     */
    public function setSignalId($signalId)
    {
        $this->signalId = $signalId;
    }

    /**
     * @return float the value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value the value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }


    /**
     * @return Graph the graph this data belongs to
     * @throws \Exception if database access fails
     */
    public function getGraph() {
        return $this->graphRepo->getById($this->graphId);
    }

    /**
     * @return GraphSignal the signal this data belongs to
     * @throws \Exception if database access fails
     */
    public function getSignal() {
        return $this->signalRepo->getById($this->signalId);
    }

    public function jsonSerialize()
    {
        $base = parent::jsonSerialize();
        $base['graphId'] = $this->graphId;
        $base['signalId'] = $this->signalId;
        $base['value'] = $this->value;
        return $base;
    }
}
