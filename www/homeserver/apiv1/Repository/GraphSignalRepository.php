<?php

namespace homeserver\apiv1\Repository;



use homeserver\apiv1\Model\Graphs\GraphSignal;

class GraphSignalRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.graphSignal")
     * @var string name of the db table
     */
    protected $tableName;


    /**
     * @param array $row the fetched row from the database
     * @return GraphSignal the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        /**
         * @var $model GraphSignal
         */
        $model = $this->factory->make(GraphSignal::class, [
            'id' => intval($row['id']),
            'name' => $row['title']
        ]);
        $model->setGraphId(intval($row['graphId']));
        $model->setColor($row['color']);
        $model->setType($row['type']);
        $model->setCreated(null);
        return $model;
    }
}
