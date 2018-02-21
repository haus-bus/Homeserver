<?php

namespace homeserver\apiv1\Repository;


use homeserver\apiv1\Model\Graphs\GraphData;

class GraphDataRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.graphData")
     * @var string name of the db table
     */
    protected $tableName;


    /**
     * @param array $row the fetched row from the database
     * @return GraphData the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        /**
         * @var $model GraphData
         */
        $model = $this->factory->make(GraphData::class, [
            'id' => intval($row['id'])
        ]);
//        $model = new GraphData(intval($row['id']));
        $model->setGraphId(intval($row['graphId']));
        $model->setSignalId(intval($row['signalId']));
        $created = new \DateTime();
        $created->setTimestamp($row['time']);
        $model->setCreated($created);
        $model->setValue(floatval($row['value']));
        return $model;
    }
}
