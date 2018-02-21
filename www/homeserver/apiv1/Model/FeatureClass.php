<?php

namespace homeserver\apiv1\Model;


use homeserver\apiv1\Repository\FeatureFunctionRepository;
use homeserver\apiv1\Repository\FeatureRepository;

class FeatureClass extends BaseModel
{
    /**
     * @var int $classId the firmware class id
     */
    private $classId;

    /**
     * @var string $guiControl the php file which represents the feature instances of this class
     */
    private $guiControl;

    /**
     * @var int $guiControlFunctions question: what is this for?
     */
    private $guiControlFunctions;

    /**
     * @var string $smokeTest question: what is this for?
     */
    private $smokeTest;

    /**
     * @var string $view the view, in which the object will be shown
     */
    private $view;


    /**
     * @Inject
     * @var FeatureRepository $featureRepository repo to load the instance features of this feature class
     */
    private $featureRepository;

    /**
     * @Inject
     * @var FeatureFunctionRepository $featureFunctionRepository repo to load the functions of this feature class
     */
    private $featureFunctionRepository;



    /**
     * @return int the firmware class id
     */
    public function getClassId()
    {
        return $this->classId;
    }

    /**
     * @param int $classId the firmware class id
     */
    public function setClassId($classId)
    {
        $this->classId = $classId;
    }

    /**
     * @return string the php file which represents the feature instances of this class
     */
    public function getGuiControl()
    {
        return $this->guiControl;
    }

    /**
     * @param string $guiControl the php file which represents the feature instances of this class
     */
    public function setGuiControl($guiControl)
    {
        $this->guiControl = $guiControl;
    }

    /**
     * @return int question: what is this for?
     */
    public function getGuiControlFunctions()
    {
        return $this->guiControlFunctions;
    }

    /**
     * @param int $guiControlFunctions question: what is this for?
     */
    public function setGuiControlFunctions($guiControlFunctions)
    {
        $this->guiControlFunctions = $guiControlFunctions;
    }

    /**
     * @return string question: what is this for?
     */
    public function getSmokeTest()
    {
        return $this->smokeTest;
    }

    /**
     * @param string $smokeTest question: what is this for?
     */
    public function setSmokeTest($smokeTest)
    {
        $this->smokeTest = $smokeTest;
    }

    /**
     * @return string the view, in which the object will be shown
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param string $view the view, in which the object will be shown
     */
    public function setView($view)
    {
        $this->view = $view;
    }


    /**
     * @return Feature[] the feature instances of this feature class
     * @throws \Exception if the instances could not be resolved
     */
    public function getFeatureInstances() {
        return $this->featureRepository->query(array("featureClassesId" => "= " . $this->getId()));
    }

    /**
     * @return FeatureFunction[] the feature functions of this feature class
     * @throws \Exception if the functions could not be resolved
     */
    public function getFeatureFunctions() {
        return $this->featureFunctionRepository->query(array("featureClassesId" => "= " . $this->getId()));
    }


    public function jsonSerialize()
    {
        $base = parent::jsonSerialize();
        $base['classId'] = $this->classId;
        $base['guiControl'] = $this->guiControl;
        $base['guiControlFunctions'] = $this->guiControlFunctions;
        $base['smokeTest'] = $this->smokeTest;
        $base['view'] = $this->view;
        return $base;
    }
}
