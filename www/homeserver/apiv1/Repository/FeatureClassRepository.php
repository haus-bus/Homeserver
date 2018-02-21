<?php

namespace homeserver\apiv1\Repository;


use Exception;
use homeserver\apiv1\Model\BaseModel;
use homeserver\apiv1\Model\Feature;
use homeserver\apiv1\Model\FeatureClass;
use homeserver\apiv1\Model\Room;

class FeatureClassRepository extends BaseRepository
{

    /**
     * @Inject("tablenames.featureClass")
     * @var string name of the db table
     */
    protected $tableName;

    /**
     * @param array $row the fetched row from the database
     * @return FeatureClass the object instance
     * @throws \Exception
     */
    protected function createInstance(array $row)
    {
        $featureClass = $this->factory->make(FeatureClass::class, [
            'id' => intval($row['id']),
            'name' => $row['name']
        ]);
        $featureClass->setClassId(intval($row['classId']));
        $featureClass->setGuiControl($row['guiControl']);
        $featureClass->setGuiControlFunctions($row['guiControlFunctions']);
        $featureClass->setSmoketest($row['smoketest']);
        $featureClass->setView($row['view']);
        $featureClass->setCreated(null);
        return $featureClass;
    }
}
