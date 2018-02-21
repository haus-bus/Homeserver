<?php

namespace homeserver\apiv1\Repository;



use homeserver\apiv1\Model\Parameters\FeatureFunctionBitmask;

class FeatureFunctionBitmaskRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.featureFunctionBitmasks")
     * @var string $tableName
     */
    protected $tableName;

    /**
     * @param array $row the fetched row from the database
     * @return FeatureFunctionBitmask the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        $featureFuncBitmask = $this->factory->make(FeatureFunctionBitmask::class, [
            'id' => intval($row['id']),
            'name' => $row['name'],
        ]);
        $featureFuncBitmask->setFeatureFunctionId(intval($row['featureFunctionId']));
        $featureFuncBitmask->setParamId(intval($row['paramId']));
        $featureFuncBitmask->setBit(intval($row['bit']));
        $featureFuncBitmask->setCreated(null);
        return $featureFuncBitmask;
    }
}
