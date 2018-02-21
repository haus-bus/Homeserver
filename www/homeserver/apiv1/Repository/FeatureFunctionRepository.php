<?php

namespace homeserver\apiv1\Repository;


use homeserver\apiv1\Model\BaseModel;
use homeserver\apiv1\Model\FeatureFunction;

class FeatureFunctionRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.featureFunction")
     * @var string $tableName
     */
    protected $tableName;

    /**
     * @param array $row the fetched row from the database
     * @return FeatureFunction the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        $featureFunc = $this->factory->make(FeatureFunction::class, [
            'id' => intval($row['id']),
            'name' => $row['name']
        ]);
        $featureFunc->setFeatureClassesId(intval($row['featureClassesId']));
        $featureFunc->setFunctionId(intval($row['functionId']));
        $featureFunc->setType($row['type']);
        $featureFunc->setView($row['view']);
        $featureFunc->setCreated(null);
        return $featureFunc;
    }
}
