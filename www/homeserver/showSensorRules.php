<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($action=="chooseSignalAlias")
{
	$message="alias: $featureInstanceId";
}

setupTreeAndContent("showSensorRules_design.html", $message);


$elementsTag = getTag("%ELEMENTS%",$html);
$elements="";
$erg = QUERY("select ruleId, rules.groupId,single,groups.name as groupName, groupFeatures.featureInstanceId from rulesignals join rules on (rulesignals.ruleId=rules.id) join groups on (groupId=groups.id) join groupFeatures on (groups.id = groupFeatures.groupId) where rulesignals.featureInstanceId='$id' group by groupId ");
while($obj=mysqli_fetch_OBJECT($erg))
{
  $actTag = $elementsTag;
  $actTag = str_replace("%LAST_OPEN%",$_SESSION["groupLinkNr".$obj->groupId]-1,$actTag);
  $actTag = str_replace("%GROUP_ID%",$obj->groupId,$actTag);
  if ($obj->single==1)
  {
     $erg2 = QUERY("select featureInstances.name as featureInstanceName, 
  	            featureClasses.name as featureClassName,
  	            roomFeatures.roomId,
  	            rooms.name as roomName
  	            
                from featureInstances 
                join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                left join roomFeatures on (roomFeatures.featureInstanceId=featureInstances.id)
                left join rooms on (rooms.id = roomFeatures.roomId)
                where featureInstances.id='$obj->featureInstanceId' limit 1");
     if ($obj2=mysqli_fetch_OBJECT($erg2))
     {
     	 $actTag = str_replace("%NAME%",$obj2->roomName." » ".$obj2->featureClassName." » ".$obj2->featureInstanceName,$actTag);
     }
     else $actTag = str_replace("%NAME%","nicht zugeordnet",$actTag);
     
  	
  	$actTag = str_replace("%TYP%","",$actTag);
  }
  else
  {
    $actTag = str_replace("%TYP%","Gruppe",$actTag);
    $actTag = str_replace("%NAME%",$obj->groupName,$actTag);
  }
  $elements.=$actTag;
	
}

$html = str_replace("%ELEMENTS%", $elements, $html);
$html = str_replace("%ID%", $id, $html);

show();
?>

