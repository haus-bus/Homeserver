<?php

namespace homeserver\apiv1\Model\Graphs;


use homeserver\apiv1\Model\BaseModel;
use homeserver\apiv1\Repository\GraphRepository;
use homeserver\apiv1\Repository\GraphSignalEventRepository;


class GraphSignal extends BaseModel
{
    /**
     * @var int $graphId the ID of the graph to which this signal belongs too
     */
    private $graphId;

    /**
     * @var int $color the rgb color value
     */
    private $color;

    /**
     * @var string $type the type of the signal, e.g. spline
     */
    private $type;


    /**
     * @Inject
     * @var GraphRepository $graphRepo
     */
    private $graphRepo;

    /**
     * @Inject
     * @var GraphSignalEventRepository $eventRepo
     */
    private $eventRepo;


    /**
     * @return int the ID of the graph to which this signal belongs too
     */
    public function getGraphId()
    {
        return $this->graphId;
    }

    /**
     * @param int $graphId the ID of the graph to which this signal belongs too
     */
    public function setGraphId($graphId)
    {
        $this->graphId = $graphId;
    }

    /**
     * @return int the rgb color value
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param int $color the rgb color value
     */
    public function setColor ($color)
    {
        $this->color = $color;
    }


    /**
     * @return string the type of the signal, e.g. spline
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type the type of the signal, e.g. spline
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return Graph the graph this signal belongs to
     * @throws \Exception if database access fails
     */
    public function getGraph() {
        return $this->graphRepo->getById($this->graphId);
    }


    /**
     * @return GraphSignalEvent[] the events belonging to this signal
     * @throws \Exception if database access fails
     */
    public function getEvents() {
        return $this->eventRepo->query(array('graphSignalsId' => "= " . $this->id));
    }


    public function jsonSerialize()
    {
        $base = parent::jsonSerialize();
        $base['graphId'] = $this->graphId;
        $base['color'] = $this->color;
        $base['type'] = $this->type;
        return $base;
    }
}
