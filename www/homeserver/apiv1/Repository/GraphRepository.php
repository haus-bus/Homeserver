<?php

namespace homeserver\apiv1\Repository;


use homeserver\apiv1\Model\Graphs\Graph;

class GraphRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.graph")
     * @var string name of the db table
     */
    protected $tableName;


    /**
     * @param array $row the fetched row from the database
     * @return Graph the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        /**
         * @var $model Graph
         */
        $model = $this->factory->make(Graph::class, [
            'id' => intval($row['id']),
            'name' => $row['title']
        ]);
        $model->setTheme($row['theme']);
        $model->setType($row['type']);
        $model->setTimeMode($row['timeMode']);
        $model->setTimeParam1(intval($row['timeParam1']));
        $model->setTimeParam2(intval($row['timeParam2']));
        $model->setWidth(intval($row['width']));
        $model->setHeight(intval($row['height']));
        $model->setHeightMode($row['heightMode']);
        $model->setDistValue(intval($row['distValue']));
        $model->setDistType($row['distType']);
        $model->setCreated(null);
        return $model;
    }
}
