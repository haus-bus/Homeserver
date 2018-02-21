<?php

namespace homeserver\apiv1\Repository;



use homeserver\apiv1\Model\Graphs\GraphSignalEvent;

class GraphSignalEventRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.graphSignalEvent")
     * @var string name of the db table
     */
    protected $tableName;


    /**
     * @param array $row the fetched row from the database
     * @return GraphSignalEvent the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        /**
         * @var $model GraphSignalEvent
         */
        $model = $this->factory->make(GraphSignalEvent::class, [
            'id' => intval($row['id'])
        ]);
        $model->setGraphSignalsId(intval($row['graphSignalsId']));
        $model->setFeatureInstanceId(intval($row['featureInstanceId']));
        $model->setFunctionId(intval($row['functionId']));
        $model->setGraphValueFunction($row['fkt']);
        $model->setCreated(null);
        return $model;
    }
}
