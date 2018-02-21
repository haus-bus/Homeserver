<?php

namespace homeserver\apiv1\Repository;


use homeserver\apiv1\Model\Parameters\FeatureFunctionEnum;

class FeatureFunctionEnumRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.featureFunctionEnums")
     * @var string $tableName
     */
    protected $tableName;

    /**
     * @param array $row the fetched row from the database
     * @return FeatureFunctionEnum the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        $featureFuncEnum = $this->factory->make(FeatureFunctionEnum::class, [
            'id' => intval($row['id']),
            'name' => $row['name'],
        ]);
        $featureFuncEnum->setFeatureFunctionId(intval($row['featureFunctionId']));
        $featureFuncEnum->setParamId(intval($row['paramId']));
        $featureFuncEnum->setValue(intval($row['value']));
        $featureFuncEnum->setCreated(null);
        return $featureFuncEnum;
    }
}
