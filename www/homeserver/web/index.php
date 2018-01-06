<?php
include("../include/all.php");

$html = file_get_contents("index_design.html");

$rolloTag = getTag("%OPT_ROLLO%",$html);
$erg = MYSQL_QUERY("select paramKey,paramValue from basicConfig where paramKey='webRollo1' or paramKey='webRollo2' or paramKey='webRollo3' or paramKey='webRollo4' limit 4") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	if ($obj->paramValue<10) $obj->paramValue="&nbsp;&nbsp;".$obj->paramValue;
	if ($obj->paramKey=="webRollo1") $rolloTag = str_replace("%BUTTON1%", $obj->paramValue, $rolloTag);
	else if ($obj->paramKey=="webRollo2") $rolloTag = str_replace("%BUTTON2%", $obj->paramValue, $rolloTag);
	else if ($obj->paramKey=="webRollo3") $rolloTag = str_replace("%BUTTON3%", $obj->paramValue, $rolloTag);
	else if ($obj->paramKey=="webRollo4") $rolloTag = str_replace("%BUTTON4%", $obj->paramValue, $rolloTag);
}
$html = str_replace("%OPT_ROLLO%","",$html);

$dimmerTag = getTag("%OPT_DIMMER%",$html);
$erg = MYSQL_QUERY("select paramKey,paramValue from basicConfig where paramKey='webDimmer1' or paramKey='webDimmer2' or paramKey='webDimmer3' or paramKey='webDimmer4' limit 4") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	if ($obj->paramValue<10) $obj->paramValue="&nbsp;&nbsp;".$obj->paramValue;
	if ($obj->paramKey=="webDimmer1") $dimmerTag = str_replace("%BUTTON1%", $obj->paramValue, $dimmerTag);
	else if ($obj->paramKey=="webDimmer2") $dimmerTag = str_replace("%BUTTON2%", $obj->paramValue, $dimmerTag);
	else if ($obj->paramKey=="webDimmer3") $dimmerTag = str_replace("%BUTTON3%", $obj->paramValue, $dimmerTag);
	else if ($obj->paramKey=="webDimmer4") $dimmerTag = str_replace("%BUTTON4%", $obj->paramValue, $dimmerTag);
}
$html = str_replace("%OPT_DIMMER%","",$html);

$schalterTag = getTag("%OPT_SCHALTER%",$html);
$html = str_replace("%OPT_SCHALTER%","",$html);

$currentReaderTag = getTag("%OPT_CURRENT_READER%",$html);
$html = str_replace("%OPT_CURRENT_READER%","",$html);

$oneTasterTag = getTag("%OPT_ONE_TASTER%",$html);
$html = str_replace("%OPT_ONE_TASTER%","",$html);

$multiTasterTag = getTag("%OPT_MULTI_TASTER%",$html);
$html = str_replace("%OPT_MULTI_TASTER%","",$html);

$tasterLabelTag = getTag("%OPT_TASTER_LABEL%",$html);
$html = str_replace("%OPT_TASTER_LABEL%","",$html);

$tasterTag = getTag("%OPT_TASTER%",$html);
$html = str_replace("%OPT_TASTER%","",$html);

$multiRowTag = getTag("%MULTITASTER_ROW%",$html);
$multiTasterPanelTag = getTag("%MULTITASTER_PANELS%",$html);

$erg = MYSQL_QUERY("select paramKey,paramValue from basicConfig where paramKey='webRoomTemp' or paramKey='webRoomHumidity' limit 2") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	if ($obj->paramKey=="webRoomTemp") $webRoomTemp=$obj->paramValue;
	else if ($obj->paramKey=="webRoomHumidity") $webRoomHumidity=$obj->paramValue;
}

$menuTag=getTag("%MENU%",$html);
$menus="";
$i=0;
$erg = MYSQL_QUERY("select id,name from rooms order by name") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	$actTag = $menuTag;
	$anzeige=$obj->name;
	$actTag = str_replace("%ROOM_NAME%",$obj->name,$actTag);
	$actTag = str_replace("%ROOM_ID%",$obj->id,$actTag);
	$menus.=$actTag;
	
	$i++;
	//if ($i%6==0) $menus.="</tr><tr>";
}
$html = str_replace("%MENU%",$menus,$html);

$raumTag = getTag("%OPT_RAUM%",$html);

/*$dimmerClassesId=getClassesIdByName("Dimmer");
$schalterClassesId=getClassesIdByName("Schalter");
$rolloClassesId=getClassesIdByName("Rollladen");
$tempClassesId=getClassesIdByName("Temperatursensor");
$feuchteClassesId=getClassesIdByName("Feuchtesensor");
*/

$where="1=2 ";
$erg = MYSQL_QUERY("SELECT count( id ) AS childs, group_concat(objectId) AS members, parentInstanceId FROM featureinstances WHERE parentInstanceId >0 and featureClassesId=1 GROUP BY parentInstanceId") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	$multitaster[$obj->parentInstanceId]=$obj->members;
	if ($obj->childs>1) $where.="or featureInstances.id='$obj->parentInstanceId' ";
}

$erg = MYSQL_QUERY("select functionData,featureInstances.id from lastreceived join featureInstances on (featureInstances.objectId=lastreceived.senderObj) where $where") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	$data=unserialize($obj->functionData)->paramData;
	
	$newMembers="";
	foreach($data as $dummy=>$o)
	{
		$searchInstance=$o->dataValue;
		$parts = explode(",",$multitaster[$obj->id]);
		foreach($parts as $dummy=>$act)
		{
			 if (getInstanceId($act)==$searchInstance)
			 {
			 	  $newMembers.=$act.",";
			 	  break;
			 }
		}
	}
	$multitaster[$obj->id]=substr($newMembers,0,strlen($newMembers)-1);
}


$objects="";
$myObjects="";
$i=0;
$erg = QUERY("select rooms.id as roomId, rooms.name as roomName,
                     featureClasses.id as classesId, featureClasses.name as classesName,
                     featureInstances.name as featureInstanceName, objectId, featureInstances.id as featureInstanceId
                     from rooms 
                     join roomFeatures on (rooms.id = roomFeatures.roomId)
                     join featureInstances on (featureInstances.id=featureInstanceId) 
                     join featureClasses ON ( featureClasses.id = featureInstances.featureClassesId)
                     where (parentInstanceId=0 or parentInstanceId is null) and (featureClassesId!='$CONTROLLER_CLASSES_ID' or featureClassesId is null)
                     and (featureClasses.name='Dimmer' or featureClasses.name='Schalter' or featureClasses.name='CurrentReader' or featureClasses.name='Rollladen' or featureClasses.name='Temperatursensor' or featureClasses.name='Feuchtesensor' or featureClasses.name='LogicalButton' or featureClasses.name='Taster')
                               order by roomName,FIND_IN_SET(featureClasses.name,'Dimmer,Schalter,Rollladen,LogicalButton,Taster,Temperatursensor,Feuchtesensor'), featureInstances.name");
while ( $obj = MYSQL_FETCH_OBJECT($erg))
{
	//if ($obj->classesName!="Temperatursensor" && $obj->classesName!="Feuchtesensor") continue;
	$elements[$obj->roomName][$obj->classesName][$obj->featureInstanceName]=$obj;
	$roomIds[$obj->roomName]=$obj->roomId;
	
	if ($obj->classesName!="LogicalButton" && $obj->classesName!="Taster")
	{
	  $objects.="&object".$i++."=".$obj->objectId;
	
	  $myObjects.="var newObject = new Array();";
	  $myObjects.="newObject['status']=-1;";
	  $myObjects.="newObject['text']=-1;";
	  $myObjects.="newObject['room']=".$obj->roomId.";";
	  $myObjects.="newObject['type']='".$obj->classesName."';";
	  $myObjects.="myObjects['".$obj->objectId."']=newObject;\n";
	}
}


$objects="objects=".$i.$objects;
$html = str_replace("%OBJECTS%",$objects,$html);
$html = str_replace("%MY_OBJECTS%",$myObjects,$html);

$results="";
foreach($elements as $room=>$arr)
{
	$actTag = $raumTag;
	$actTag = str_replace("%RAUM%",$room,$actTag);
	$actTag = str_replace("%ROOM_ID%",$roomIds[$room],$actTag);
	
	$tempContent="";
	$tempTag = getTag("%OPT_TEMP%",$actTag);
	$feuchteContent="";
	$feuchteTag = getTag("%OPT_FEUCHTE%",$actTag);
	$tempContent2="";
	$tempTag2 = getTag("%OPT_TEMP_2%",$actTag);
	$feuchteContent2="";
	$feuchteTag2 = getTag("%OPT_FEUCHTE_2%",$actTag);
	
	$actElements="";
		
	foreach($arr as $class=>$arra)
  {
 	  if ($class=="Dimmer") $actClassTag = $dimmerTag;
 	  else if ($class=="Schalter") $actClassTag = $schalterTag;
 	  else if ($class=="Rollladen") $actClassTag = $rolloTag;
 	  else if ($class=="CurrentReader")
 	  {
 	  	 $myObjectId="";
 	  	 foreach($arra as $instance=>$obj)
       {
       	 $myObjectId=$obj->objectId;
       	 break;
       }

 	  	 $actClassTag = $currentReaderTag;
       $erg = MYSQL_QUERY("select paramKey,paramValue from basicConfig where paramKey='current1d' or paramKey='current7d' or paramKey='current30d' limit 3") or die(MYSQL_ERROR());
       while($obj=MYSQL_FETCH_OBJECT($erg))
       {
       	 if ($obj->paramKey=="current1d") $actClassTag = str_replace("%CURRENT_1D%",$obj->paramValue." kWh",$actClassTag);
       	 else if ($obj->paramKey=="current7d") $actClassTag = str_replace("%CURRENT_7D%",$obj->paramValue." kWh",$actClassTag);
       	 else if ($obj->paramKey=="current30d") $actClassTag = str_replace("%CURRENT_30D%",$obj->paramValue." kWh",$actClassTag);
       }
 	  }
 	  else if ($class=="Temperatursensor")
 	  {
 	  	$i=0;
 	  	foreach($arra as $instance=>$obj)
      {
      	if ($i==0)
      	{
   	  	  $tempContent = $tempTag;
   	      $tempContent = str_replace("%MY_ID%",$obj->objectId,$tempContent);
		      if ($webRoomTemp==1) $html = str_replace("%MY_TEMP_ROOM_".$roomIds[$room]."%",$obj->objectId,$html);
		    }
		    else if ($i==1)
      	{
   	  	  $tempContent2 = $tempTag2;
   	      $tempContent2 = str_replace("%MY_ID%",$obj->objectId,$tempContent2);
		      if ($webRoomTemp==1) $html = str_replace("%MY_TEMP_ROOM2_".$roomIds[$room]."%",$obj->objectId,$html);
		    }
		    else break;
		    $i++;
   	  }
   	  continue;
 	  }
 	  else if ($class=="Feuchtesensor")
 	  {
 	  	$i=0;
 	  	foreach($arra as $instance=>$obj)
      {
      	if ($i==0)
      	{
 	  	    $feuchteContent = $feuchteTag;
   	      $feuchteContent = str_replace("%MY_ID%",$obj->objectId,$feuchteContent);
		      if ($webRoomHumidity==1) $html = str_replace("%MY_HUMIDITY_ROOM_".$roomIds[$room]."%",$obj->objectId,$html);
		    }
		    else if ($i==1)
      	{
 	  	    $feuchteContent2 = $feuchteTag2;
   	      $feuchteContent2 = str_replace("%MY_ID%",$obj->objectId,$feuchteContent2);
		      if ($webRoomHumidity==1) $html = str_replace("%MY_HUMIDITY_ROOM2_".$roomIds[$room]."%",$obj->objectId,$html);
		    }
		    else
   	      break;
   	      
   	    $i++;
   	  }
   	  continue;
 	  }
 	  else if ($class=="LogicalButton")
 	  {
 	  	$actFeatureTag = $tasterTag;
 	  	 	  	
      unset($myTaster);
      $i=0;
      $tmp="";
      
      foreach($arra as $instance=>$obj)
      {
      	if ($multitaster[$obj->featureInstanceId]!="")
      	{
      		$members = explode(",",$multitaster[$obj->featureInstanceId]);
      		if (count($members)==1)
      		{
        		$actTasterTag = $oneTasterTag;
        		$actTasterTag = str_replace("%MY_ID%",$members[0],$actTasterTag);
      		}
      		else
      		{
      		  $actTasterTag = $multiTasterTag;
      		  $actTasterTag = str_replace("%MULTI_TASTER_ID%",$obj->objectId,$actTasterTag);
      		
      		  $actMultiTag = $multiTasterPanelTag;
      		
      		  $multiRows="";
      		  for ($ii=0;$ii<count($members)/2;$ii++)
      		  {
          		$actMultiRowTag=$multiRowTag;
        		  $actMultiRowTag = str_replace("%MY_FIRST_ID%",$members[$ii*2],$actMultiRowTag);
        		  $actMultiRowTag = str_replace("%MY_SECOND_ID%",$members[$ii*2+1],$actMultiRowTag);
        		  $multiRows.=$actMultiRowTag;
      		  }
      		
      		  $actMultiTag = str_replace("%MULTI_TASTER_ID%",$obj->objectId,$actMultiTag);
      		  $actMultiTag = str_replace("%MULTITASTER_ROW%",$multiRows,$actMultiTag);
      		  $multiTasterPanels.=$actMultiTag;
      		}
      	}
      	
      	$actTasterTag = str_replace("%TASTER_LABEL%",$instance,$actTasterTag);
      	$myTaster[$i++]=$actTasterTag;
      	if ($i==4) break;
      }
      
      
      for ($a=$i;$a<5;$a++)
      {
      	$myTaster[$a]='<td width="50%"></td>';
      }
      
 	  	if ($inverted==1)
 	  	{
 	  		$actFeatureTag = str_replace("%POS1%",$myTaster[4],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS2%",$myTaster[3],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS3%",$myTaster[2],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS4%",$myTaster[1],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS5%",$myTaster[0],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS6%",$tasterLabelTag,$actFeatureTag);
 	  	}
 	  	else
 	  	{
 	  		$actFeatureTag = str_replace("%POS1%",$tasterLabelTag,$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS2%",$myTaster[4],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS3%",$myTaster[3],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS4%",$myTaster[2],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS5%",$myTaster[1],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS6%",$myTaster[0],$actFeatureTag);
 	  	}
	  	
 	  	$actElements.=$actFeatureTag;
 	  	continue;
 	  }
 	  else if ($class=="Taster")
 	  {
 	  	$actFeatureTag = $tasterTag;
 	  	 	  	
      unset($myTaster);
      $i=0;
      $tmp="";
      
      foreach($arra as $instance=>$obj)
      {
     		$actTasterTag = $oneTasterTag;
     		$actTasterTag = str_replace("%MY_ID%",$obj->objectId,$actTasterTag);
      	$actTasterTag = str_replace("%TASTER_LABEL%",$instance,$actTasterTag);
      	$myTaster[$i++]=$actTasterTag;
      	if ($i==5) break;
      }
      
      for ($a=$i;$a<5;$a++)
      {
      	$myTaster[$a]='<td width="50%"></td>';
      }
      
 	  	if ($inverted==1)
 	  	{
 	  		$actFeatureTag = str_replace("%POS1%",$myTaster[4],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS2%",$myTaster[3],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS3%",$myTaster[2],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS4%",$myTaster[1],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS5%",$myTaster[0],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS6%",$tasterLabelTag,$actFeatureTag);
 	  	}
 	  	else
 	  	{
 	  		$actFeatureTag = str_replace("%POS1%",$tasterLabelTag,$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS2%",$myTaster[4],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS3%",$myTaster[3],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS4%",$myTaster[2],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS5%",$myTaster[1],$actFeatureTag);
 	  		$actFeatureTag = str_replace("%POS6%",$myTaster[0],$actFeatureTag);
 	  	}
	  	
 	  	$actElements.=$actFeatureTag;
 	  	continue;
 	  }
 	
	  foreach($arra as $instance=>$obj)
    {
  	   $actFeatureTag = $actClassTag;
   	   $actFeatureTag = str_replace("%MY_ID%",$obj->objectId,$actFeatureTag);
   	   $actFeatureTag = str_replace("%TEXT%",$instance,$actFeatureTag);
   	   
   	   $left = getTag("%LEFT%",$actFeatureTag);
   	   $right = getTag("%RIGHT%",$actFeatureTag);
   	   
   	   if ($inverted==1)
   	   {
   	     $actFeatureTag = str_replace("%LEFT%",$right,$actFeatureTag);
   	     $actFeatureTag = str_replace("%RIGHT%",$left,$actFeatureTag);
   	   }
   	   else
   	   {
   	     $actFeatureTag = str_replace("%LEFT%",$left,$actFeatureTag);
   	     $actFeatureTag = str_replace("%RIGHT%",$right,$actFeatureTag);
   	   }
   	   
   	   $actElements.=$actFeatureTag;
    }
  }
  
  
  if ($tempContent!="" && $feuchteContent!="") chooseTag("%OPT_BOTH%",$actTag);
  else removeTag("%OPT_BOTH%",$actTag);
  $actTag = str_replace("%OPT_TEMP%",$tempContent,$actTag);
  $actTag = str_replace("%OPT_FEUCHTE%",$feuchteContent,$actTag);

  if ($tempContent2!="" && $feuchteContent2!="") chooseTag("%OPT_BOTH_2%",$actTag);
  else removeTag("%OPT_BOTH_2%",$actTag);
  $actTag = str_replace("%OPT_TEMP_2%",$tempContent2,$actTag);
  $actTag = str_replace("%OPT_FEUCHTE_2%",$feuchteContent2,$actTag);
    
  $actTag = str_replace("%ELEMENTS%",$actElements,$actTag);
  $results.=$actTag;
  
}


$html = str_replace("%OPT_RAUM%",$results,$html);
$html = str_replace("%MULTITASTER_PANELS%",$multiTasterPanels,$html);

$useragent=$_SERVER['HTTP_USER_AGENT'];
if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
{
	 $html = str_replace("onclick","ontouchstart",$html);
	 //$html = str_replace("onmouseup","ontouchend",$html);
}

die($html);
?>