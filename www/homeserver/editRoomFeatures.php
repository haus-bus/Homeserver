<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted!="")
{
   QUERY("DELETE from roomFeatures where roomId='$id'");
  trace("roomFeatures von room mit id $id glöscht");
   
   $erg = QUERY("select id from featureInstances");
   while($obj=mysqli_fetch_OBJECT($erg))
   {
      $act="id".$obj->id;
      $act=$$act;
      if ($act==1)
      {
      	QUERY("INSERT into roomFeatures (roomId, featureInstanceId) values('$id','$obj->id')") ;
      	/*$erg2 = QUERY("select id from featureInstances where parentInstanceId='$obj->id'");
      	while($row2=mysqli_fetch_row($erg2))
      	{
      		QUERY("INSERT into roomFeatures (roomId, featureInstanceId) values('$id','$row2[0]')") ;
      	}*/
      }
   }
   
   header("Location: editRoom.php?id=$id");
   exit;
}

setupTreeAndContent("editRoomFeatures_design.html");

$html = str_replace("%ID%",$id, $html);

$erg = QUERY("select name from rooms where id='$id' limit 1");
if ($obj=mysqli_fetch_OBJECT($erg))
{
   $html = str_replace("%ROOM_NAME%",$obj->name, $html); 
}
else die("Fehler! Ungültige ID $id");

$erg = QUERY("select featureInstanceId,roomId from roomFeatures");
while($obj=mysqli_fetch_OBJECT($erg))
{
	if ($obj->roomId==$id) $roomFeatures[$obj->featureInstanceId]=1;
	$allRoomFeatures[$obj->featureInstanceId]=1;
}


$closeTreeFolder="</ul></li> \n";
      
$treeElements="";
$treeElements.=addToTree("<a href='editRoom.php?id=$id&isFolder=$isFolder'>Feature zum Raum hinzufügen</a>",1);
$html=str_replace("%INITIAL_ELEMENT2%","expandToItem('tree2','$treeElementCount');",$html);

$lastController="";
$erg = QUERY("select controller.id as controllerId, controller.name as controllerName, online,
                           featureInstances.id as featureInstanceId, featureInstances.name as featureInstanceName,
                           featureClasses.name as featureClassName
                           from controller
                           join featureInstances on (featureInstances.controllerId = controller.id)
                           join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                           order by controllerName,featureClassName,featureInstanceName"); // where parentInstanceId='0'
while($obj = mysqli_fetch_object($erg))
{
 	if ($obj->controllerId != $lastController)
  {
    if ($lastController!="")
    {
      $treeElements.=$closeTreeFolder; // letzte class
      $treeElements.=$closeTreeFolder; // letzter controller
 		}
  	$lastController=$obj->controllerId;
  	$lastClass="";
  	
    $status = "<img src='img/online2.gif'>";
    if ($obj->online=="0") $status = "<img src='img/offline2.gif'>";
 		$treeElements.=addToTree($status." ".$obj->controllerName,1);
  }

 	if ($obj->featureClassName != $lastClass)
  {
    if ($lastClass!="")
    {
      $treeElements.=$closeTreeFolder; // letzte class
 		}
  	$lastClass=$obj->featureClassName;

 		$treeElements.=addToTree($obj->featureClassName,1);
  }
  
  if ($roomFeatures[$obj->featureInstanceId]==1)
  {
  	$checked="checked";
  	$assigned.="expandToItem('tree2',".$treeElementCount.");\n";
  }
  else $checked="";
  
  $treeElements.=addToTree("<input type='checkbox' name='id$obj->featureInstanceId' value='1' $checked>$obj->featureInstanceName",0);
  if ($allRoomFeatures[$obj->featureInstanceId]!=1) $notAssigned.="- <a href=\"javascript:expandToItem('tree2','$treeElementCount')\">$obj->controllerName , $obj->featureClassName , $obj->featureInstanceName</a><br>";
}     

$treeElements.=$closeTreeFolder; // letzte class
$treeElements.=$closeTreeFolder; // letzter controller

$html = str_replace("%TREE_ELEMENTS%",$treeElements,$html);
$html = str_replace("%ASSIGNED%",$assigned,$html);
$html = str_replace("%NOT_ASSIGNED%",$notAssigned,$html);

show();

?>