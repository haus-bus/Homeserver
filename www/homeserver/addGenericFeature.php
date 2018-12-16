<?php
include_once ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($test==1)
{
	  $params=paramsToUrl("action=addSensor&herm=1&second=2&third=3");
	  die("<script>top.location='addGenericFeature.php?classes=Schalter,Dimmer&params=$params';</script>");
}

$selectedClasses="";
$classesParts = explode(",",$classes);
foreach ($classesParts as $className)
{
	if ($selectedClasses!="") $selectedClasses.=" or ";
	$actId = getClassesIdByName($className);
	$selectedClasses.="featureInstances.featureClassesId='$actId'";
}

setupTreeAndContent("addGenericFeature_design.html");

$html = str_replace("%PARAMS%",$params,$html);
$html = str_replace("%RETURN_URL%",$returnUrl,$html);

$closeTreeFolder = "</ul></li> \n";

$treeElements = "";
$treeElements .= addToTree("<a href='$returnUrl'>Feature auswählen</a>", 1);
$html = str_replace("%INITIAL_ELEMENT2%", "expandToItem('tree2','$treeElementCount');", $html);

unset($ready);
$lastRoom = "";
$erg = QUERY("select rooms.id as roomId, rooms.name as roomName,
                     roomFeatures.featureInstanceId,
                     featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                     featureClasses.name as featureClassName
                     from rooms
                     join roomFeatures on (roomFeatures.roomId = rooms.id)
                     join featureInstances on (featureInstances.id = roomFeatures.featureInstanceId)
                     join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                     where ($selectedClasses)
                     order by roomName,featureClassName,featureInstanceName");
while ( $obj = mysqli_fetch_object($erg) )
{
  $ready[$obj->featureInstanceId] = 1;
  
  if ($obj->roomId != $lastRoom)
  {
    if ($lastRoom != "")
    {
      $treeElements .= $closeTreeFolder; // letzte featureclass
      $treeElements .= $closeTreeFolder; // letzter raum
    }
    $lastRoom = $obj->roomId;
    $treeElements .= addToTree($obj->roomName, 1);
    $lastClass = "";
  }
  
  if ($obj->featureClassesId != $lastClass)
  {
    if ($lastClass != "") $treeElements .= $closeTreeFolder; // letzte featureclass
    
    $lastClass = $obj->featureClassesId;
    $treeElements .= addToTree($obj->featureClassName, 1);
  }
  
  $treeElements .= addToTree("<a href='$returnUrl?params=$params&featureInstanceId=" . $obj->featureInstanceId."'>" . i18n($obj->featureInstanceName) . "</a>", 0);
}

$treeElements .= $closeTreeFolder; // letzte featureclass
$treeElements .= $closeTreeFolder; // letzter raum


$lastRoom = "";
$lastController = "";
$erg = QUERY("select featureInstances.id as featureInstanceId, featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                     featureClasses.name as featureClassName,
                     controller.id as controllerId, controller.name as controllerName
                     from featureInstances
                     join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                     join controller on (featureInstances.controllerId = controller.id)
                     join featureFunctions on (featureInstances.featureClassesId=featureFunctions.featureClassesId) 
                     where ($selectedClasses)
                     order by controllerName, featureClassName,featureInstanceName");
while ( $obj = mysqli_fetch_object($erg) )
{
  if ($ready[$obj->featureInstanceId] == 1) continue;
  
  if ($lastRoom == "")
  {
    $lastRoom = "dummy";
    $treeElements .= addToTree("Keinem Raum zugeordnet", 1);
  }
  
  if ($obj->controllerId != $lastController)
  {
    if ($lastController != "")
    {
      $treeElements .= $closeTreeFolder; // letzte class
      $treeElements .= $closeTreeFolder; // letzter controller
    }
    $lastController = $obj->controllerId;
    $treeElements .= addToTree($obj->controllerName, 1);
    $lastClass = "";
  }
  
  if ($obj->featureClassesId != $lastClass)
  {
    if ($lastClass != "") $treeElements .= $closeTreeFolder; // letzte featureclass
    
    $lastClass = $obj->featureClassesId;
    $treeElements .= addToTree($obj->featureClassName, 1);
  }
  
  $treeElements .= addToTree("<a href='$returnUrl?params=$params&featureInstanceId=" . $obj->featureInstanceId."'>" . i18n($obj->featureInstanceName) . "</a>", 0);
}

$treeElements .= $closeTreeFolder; // letzte featureclass
$treeElements .= $closeTreeFolder; // letzter controller
$treeElements .= $closeTreeFolder; // letzter raum

$html = str_replace("%TREE_ELEMENTS%", $treeElements, $html);
show();

?>