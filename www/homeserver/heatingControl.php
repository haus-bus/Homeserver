<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

// URL Variablen von addGenericFeature wieder in Variablen umwandeln
if ($params!="") urlToParams($params);

if ($action=="createNew") QUERY("INSERT into heating (sensor) values('0')");
else if ($action=="delete")
{
	 if ($confirm==1) QUERY("delete from heating where id='$id' limit 1");
	 else showMessage("Soll dieses Thermostat wirklich gelöscht werden?","Thermostat löschen?", $link = "heatingControl.php?action=delete&confirm=1&id=$id", $linkName = "Ja, löschen", $link2 = "heatingControl.php", $link2Name = "Nein, zurück");
}
else if ($action=="addSensor") QUERY("UPDATE heating set sensor='$featureInstanceId' where id='$id' limit 1");
else if ($action=="addAktor") QUERY("UPDATE heating set relay='$featureInstanceId' where id='$id' limit 1");
else if ($action=="selectDiagram")
{
	 $html = file_get_contents("templates/selectDiagram_design.html");
	 $elementsTag = getTag("%ELEMENTS%",$html);
   $elements="";
   $erg = QUERY("select id,title from graphs order by id");
   while($obj=MYSQLi_FETCH_OBJECT($erg))
   {  
   	  $actTag = $elementsTag;
	    $actTag = str_replace("%DIAGRAM_ID%",$obj->id, $actTag);
	    $actTag = str_replace("%DIAGRAM_TITLE%",$obj->title, $actTag);
	    $elements.=$actTag;
   }
   $html = str_replace("%ELEMENTS%",$elements,$html);
   $html = str_replace("%ID%",$id, $html);
   die($html);
}
else if ($action=="setDiagram") QUERY("UPDATE heating set diagram='$diagram' where id='$id' limit 1");
else if($action=="createDiagram")
{
	$erg = QUERY("select sensor, relay from heating where id='$id' limit 1");
	if ($obj=MYSQLi_FETCH_OBJECT($erg))
	{
		$data = getFeatureInstanceData($obj->sensor);
		$sensorName = $data->featureInstanceName;
		
  	QUERY("INSERT into graphs (title,timeMode,timeParam1,timeParam2,width,height,distValue,distType,theme,heightMode) 
	                    values('Thermostat $sensorName','days','7','','','700','','','','fixed')");
	  $graphId = query_insert_id();
    
    QUERY("TRUNCATE graphData");
    
    QUERY("INSERT into graphSignals (graphId, color, title) values('$graphId','FF2D0D','$sensorName')");
    $signalId = query_insert_id();
    QUERY("INSERT into graphSignalEvents (graphSignalsId, featureInstanceId, functionId,fkt) 
                                   values('$signalId','$obj->sensor','94','celsius+centiCelsius/100')"); // 94 ist FunctionId vom Temperatur Statusevent

    if ($diagramType==1 && $obj->relay>0)
    {
    	$onId=62;  //62 ist FunctionId vom Schalter evOn
    	$offId=63;  //63 ist FunctionId vom Schalter evOff
    	
		  $relayData = getFeatureInstanceData($obj->relay);
		  if ($relayData->featureClassName=="Rollladen")
		  {
		  	$onId=175;  //175 ist FunctionId vom Rollo evOpen
		  	$offId=56;  //56 ist FunctionId vom Rollo evClosed
		  }

      QUERY("INSERT into graphSignals (graphId, color, title, type) values('$graphId','086BFF','Heizungsschaltung','steps')");
      $signalId = query_insert_id();
      QUERY("INSERT into graphSignalEvents (graphSignalsId, featureInstanceId, functionId,fkt) 
                                       values('$signalId','$obj->relay','$onId','20')"); 
      QUERY("INSERT into graphSignalEvents (graphSignalsId, featureInstanceId, functionId,fkt) 
                                       values('$signalId','$obj->relay','$offId','19.5')"); 
    }
    
    QUERY("UPDATE heating set diagram='$graphId' where id='$id' limit 1");
    
	  triggerTreeUpdate();
	}
}


$html = file_get_contents("templates/heatingControl_design.html");

$elementsTag = getTag("%ELEMENTS%",$html);
$elements="";
$foundInavtive=0;
$generated=0;
$erg = QUERY("select heating.id, heating.sensor, heating.relay, heating.diagram, graphs.title as graphTitle, group_concat(rooms.name) as roomName
                     from heating 
                     left join graphs on(graphs.id=heating.diagram)
                     left join roomFeatures on(roomFeatures.featureInstanceId=heating.sensor)
                     left join rooms on(rooms.id=roomFeatures.roomId)
                     group by roomFeatures.featureInstanceId,heating.id
                     order by heating.id");
while($obj=MYSQLi_FETCH_OBJECT($erg))
{
	 $actTag = $elementsTag;
	 $actTag = str_replace("%ID%",$obj->id, $actTag);
	 
	 if ($obj->sensor==0) $mySensor="eintragen";
	 else
	 {
	 	  $data = getFeatureInstanceData($obj->sensor);
	 	  $mySensor = $data->roomName." > ".$data->featureInstanceName;
	 }
	 $actTag = str_replace("%TEMP%",$mySensor, $actTag);
	 
	 if ($obj->relay==0) $myAktor="eintragen";
	 else
	 {
	 	  $relayData = getFeatureInstanceData($obj->relay);
	 	  $myAktor = $data->roomName." > ".$relayData->featureInstanceName;
	 }
	 $actTag = str_replace("%SWITCH%",$myAktor, $actTag);
	 
	 if ($obj->diagram==0 || $obj->graphTitle==null)
	 {
	 	 if ($obj->sensor==0) $mySensor="</a>Zuerst Sensor auswählen";
	 	 else $mySensor="eintragen";
	 }
	 else $mySensor=$obj->graphTitle;
	 
	 $actInactive=0;
	 $status="<img src='/homeserver/img/offline2.gif' title='Regeln für Thermostat noch nicht generiert'>";
	 if ($obj->sensor>0 && $obj->relay>0)
	 {
	 	  $actInactive=1;
	 	  $erg2= QUERY("select groups.id as groupId,
	 	                       basicRules.id as ruleId,
	 	                       basicrulesignals.id as signalId
	 	                      
	 	                from groups 
	 	                join groupFeatures on (groupFeatures.groupId = groups.id)
	 	                join basicRules on (basicRules.groupId = groups.id)
	 	                join basicrulesignals on (basicrulesignals.ruleId=basicRules.id)
	 	                where groupFeatures.featureInstanceId='$obj->relay' and groups.single=1
	 	                      and extras='Heizungssteuerung'
	 	                      and basicrulesignals.featureInstanceId='$obj->sensor'
	 	                ");
	 	  if ($obj2=MYSQLi_FETCH_OBJECT($erg2))
	 	  {
	 	  	$status="<img src='/homeserver/img/online2.gif' title='Regeln sind generiert'>";
	 	  	$actInactive=0;
	 	  }
	 	  else if ($action=="generateRules")
	 	  {
	 	  	  $erg2= QUERY("select groups.id as groupId 
	 	  	                       from groups 
	 	  	          	           join groupFeatures on (groupFeatures.groupId = groups.id)
           	 	                 where groupFeatures.featureInstanceId='$obj->relay' and groups.single=1
     	                 ");
     	    if ($obj2=MYSQLi_FETCH_OBJECT($erg2))
     	    {
     	    	 $fktValue="0";
     	    	 if ($relayData->featureClassName=="Rollladen") $fktValue="true";
     	    	 
     	    	 QUERY("INSERT into basicRules (groupId, fkt1,fkt2,startDay,startHour,startMinute,endDay,endHour,endMinute,extras,active)
     	    	                         values('$obj2->groupId','$fktValue','true','7','31','255','7','31','255','Heizungssteuerung','1')");
     	    	 $ruleId = QUERY_INSERT_ID();

     	    	 QUERY("INSERT into basicRuleSignals (ruleId, featureInstanceId) values ('$ruleId','$obj->sensor')");
     	    	 $signalId = QUERY_INSERT_ID();
     	    	 
     	    	 $status="<img src='/homeserver/img/online2.gif' title='Regeln sind generiert'>";
	 	  	     $actInactive=0;
	 	  	     $debug.="Regel für Thermostat $mySensor wurde generiert <br>";
	 	  	     $generated=1;
     	    }
     	    else $debug.="<font color=#bb0000><b>Fehler: Gruppe für Sensor $mySensor konnte nicht gefunden werden </font><br>";
	 	  }
	 }
	 
	 if ($actInactive==1) $foundInavtive=1;
	 
	 $actTag = str_replace("%STATUS%",$status, $actTag);
	 $actTag = str_replace("%DIAGRAM%",$mySensor, $actTag);
	 $actTag = str_replace("%ROOM%",$obj->roomName, $actTag);
	 
	 $actTag = str_replace("%SENSOR_PARAMS%",paramsToUrl("action=addSensor&id=$obj->id"), $actTag);
	 $actTag = str_replace("%AKTOR_PARAMS%",paramsToUrl("action=addAktor&id=$obj->id"), $actTag);
	 
	 $elements.=$actTag;
}


$html = str_replace("%ELEMENTS%",$elements,$html);

if ($foundInavtive==1) chooseTag("%OPT_GENERATE_RULES%",$html);
else removeTag("%OPT_GENERATE_RULES%",$html);

if ($generated==1) $debug.="<font color=#bb0000><b>Achtung:</b> Nach dem Generieren neuer Regeln, müssen diese einmalig zu den Controllern übermittelt werden. Dies bitte unter System -> Controller: Regeln übermitteln durchführen oder <a href='/homeserver/editRules.php?action=submitRules' target='_blank'>hier klicken.</a></font><br>";
if ($debut!="") $debug="<br>".$debug."<br>";

$html = str_replace("%DEBUG%",$debug,$html);

die($html);

?>  