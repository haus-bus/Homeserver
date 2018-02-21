<?php

namespace homeserver\apiv1\Repository;



use homeserver\apiv1\Model\Parameters\FeatureFunctionParam;

class FeatureFunctionParamRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.featureFunctionParams")
     * @var string $tableName
     */
    protected $tableName;

    /**
     * @param array $row the fetched row from the database
     * @return FeatureFunctionParam the object instance
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function createInstance(array $row)
    {
        $className = "homeserver\\apiv1\\Model\\Parameters\\Param" . ucfirst(strtolower($row['type']));
        $featureFuncPar = $this->factory->make($className, [
            'id' => intval($row['id']),
            'name' => $row['name'],
            'description' => $row['comment']
        ]);
        $featureFuncPar->setFeatureFunctionId(intval($row['featureFunctionId']));
        $featureFuncPar->setView($row['view']);
        $featureFuncPar->setCreated(null);
        return $featureFuncPar;
    }
}
