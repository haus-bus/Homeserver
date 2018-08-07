<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted!="")
{
	$erg = QUERY("select id from featureInstances where checked='0'");
  while($obj=mysqli_fetch_OBJECT($erg))
  {
  	 $id = "obj".$obj->id;
  	 if ($$id==1) deleteFeatureInstance($obj->id);
  }

  triggerTreeUpdate();

/*
     MYSQLi_QUERY("UPDATE featureinstances set checked='0'");
     updateControllerStatus();
     sleep($CONTROLLER_READ_TIMEOUT);
     $message="Objecte wurden gelöscht und Controllerstatus aktualisiert"; 
     */
}

setupTreeAndContent("deleteObjects_design.html", $message);

$elementsTag = getTag("%ELEMENTS%",$html);
$elements="";

$erg = QUERY("select featureInstances.id as featureInstanceId, featureInstances.name as featureInstanceName,
                     featureClasses.name as featureClassName,
                     controller.name as controllerName
                     from featureInstances
                     left join featureClasses ON ( featureClasses.id = featureInstances.featureClassesId)
                     left join controller on (featureInstances.controllerId = controller.id)
                     where checked='0'
                     order by controllerName,featureClassName, featureInstanceName");
while($obj=mysqli_fetch_OBJECT($erg))
{
	 $actTag = $elementsTag;
	 $actTag = str_replace("%ID%",$obj->featureInstanceId, $actTag);
	 $actTag = str_replace("%NAME%","Controller: ".$obj->controllerName." » ".$obj->featureClassName." » ".$obj->featureInstanceName, $actTag);
	 
	 $elements.=$actTag;
}
$html = str_replace("%ELEMENTS%",$elements, $html);
if ($elements=="")
{
	getTag("%OPT%",$html);
	$html = str_replace("%OPT%","Es sind keine Objekte zum Löschen markiert",$html);
}
else chooseTag("%OPT%",$html);

show();

?>