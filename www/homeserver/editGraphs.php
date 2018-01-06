<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($action == "submitGraph")
{
  if ($delete == 1)
  {
    MYSQL_QUERY("DELETE from graphs where id='$id' limit 1") or die(MYSQL_ERROR());
    triggerTreeUpdate();
    header("Location: editGraphs.php");
    exit;
  }
  else
  {
    if ($timeType == "fixed")
    {
      $timeParam1 = selectToTime($timeParam1);
      $timeParam2 = selectToTime($timeParam2);
    }
    
    if ($id == "")
    {
      MYSQL_QUERY("INSERT into graphs (title,timeMode,timeParam1,timeParam2,width,height,distValue,distType,theme,heightMode)
                             values('$name','$timeType','$timeParam1','$timeParam2','$width','$height','$distValue','$distType','$theme','$heightMode')") or die(MYSQL_ERROR());
      $id = mysql_insert_id();
      triggerTreeUpdate();
    }
    else
    {
      MYSQL_QUERY("update graphs set theme='$theme',title='$name',timeMode='$timeType',timeParam1='$timeParam1',timeParam2='$timeParam2',width='$width',height='$height',distValue='$distValue',distType='$distType',heightMode='$heightMode' where id='$id' limit 1") or die(MYSQL_ERROR());
    }
  }
}
else if ($action == "changeSignalType")
  QUERY("update graphSignals set type='$signalType' where graphId='$id' and id='$signalId' limit 1");

if ($deleteSignal!="") 
{
	MYSQL_QUERY("delete from graphSignals where id='$deleteSignal' and graphId='$id' limit 1") or die(MYSQL_ERROR());
	MYSQL_QUERY("delete from graphSignalEvents where graphSignalsId='$deleteSignal'") or die(MYSQL_ERROR());	
}
else if( $deleteEvent!="" ) MYSQL_QUERY("delete from graphSignalEvents  where id='$deleteEvent' limit 1") or die(MYSQL_ERROR());	
else if ($setColor!="") MYSQL_QUERY("update graphSignals set color='$setColor' where id='$signalId' limit 1") or die(MYSQL_ERROR());
else if ($nameSignal!="")
{
    if ($submitted==1) MYSQL_QUERY("update graphSignals set title='$name' where id='$signalId' and graphId='$id' limit 1") or die(MYSQL_ERROR());
    else 
    {
       setupTreeAndContent("editGraphSignalName_design.html");
       $html = str_replace("%ID%", $id, $html);
       $html = str_replace("%SIGNAL_ID%", $nameSignal, $html);
       $erg = MYSQL_QUERY("select title from graphSignals where id='$nameSignal' and graphId='$id' limit 1") or die(MYSQL_ERROR());
       $row=MYSQL_FETCH_ROW($erg);
       $html = str_replace("%NAME%", $row[0], $html);
        
       show();
    }
}
else if ($action=="editFkt")
{
  if ($submitted==1) 
  {
  	MYSQL_QUERY("update graphSignalEvents set fkt='$fkt' where graphSignalsId='$signalId' and id='$signalEventId' limit 1") or die(MYSQL_ERROR());
  	QUERY("TRUNCATE graphData");
  }
  else
  {
    setupTreeAndContent("editGraphSignalFkt_design.html");
    $html = str_replace("%ID%", $id, $html);
    $html = str_replace("%SIGNAL_ID%", $signalId, $html);
    $html = str_replace("%SIGNAL_EVENT_ID%", $signalEventId, $html);
    
    $erg = MYSQL_QUERY("select functionId,fkt from graphSignalEvents where graphSignalsId='$signalId' and id='$signalEventId' limit 1") or die(MYSQL_ERROR());
    $row=MYSQL_FETCH_ROW($erg);
    $functionId = $row[0];
    $fkt = $row[1];
    $html = str_replace("%FKT%", $fkt, $html);
    
    $params="";
    $erg = MYSQL_QUERY("select name from featureFunctionParams where featureFunctionId='$functionId' order by id") or die(MYSQL_ERROR());
    while($row=MYSQL_FETCH_ROW($erg))
    {
       $params.="<li>".$row[0]."<br>";
    }
    
    if ($params=="") $params="<i>keine</i> (Bitte festen Wert eintragen)";
    $html = str_replace("%PARAMS%", $params, $html);
    

    show();
  }
}
else if($action=="deleteCache")
{
	if ($confirm==1) QUERY("TRUNCATE graphdata");
	else showMessage("Der Diagramcache speichert alle empfangenen Busdaten zwischen, so dass die Diagramme schneller angezeigt werden können.<br>Der Cache wird automatisch einmal pro Minute aktualisiert.", "Soll der Diagramcache gelöscht werden?", "editGraphs.php?action=deleteCache&id=$id&confirm=1", "Ja, Cache löschen","editGraphs.php?id=$id", "Nein, zurück");
}

setupTreeAndContent("editGraphs_design.html");
if ($id != "")
{
  $html = str_replace("%MODE%", "bearbeiten", $html);
  
  $erg = MYSQL_QUERY("select * from graphs where id='$id' limit 1") or die(MYSQL_ERROR());
  $obj = MYSQL_FETCH_OBJECT($erg);
  
  $html = str_replace("%NAME%", $obj->title, $html);
  $html = str_replace("%WIDTH%", $obj->width, $html);
  $html = str_replace("%HEIGHT%", $obj->height, $html);
  
  //$html = str_replace("%THEME_OPTIONS%", getSelect($obj->theme, "default,dark-unica,sand-signika,grid-light", "Standard,Dunkel,Sand,Gitter"), $html);
  $html = str_replace("%HEIGHT_TYPE_OPTIONS%", getSelect($obj->heightMode, ",percent,fixed", "Automatisch,Prozent der Fensterhöhe:,Feste Höhe in Pixel:"), $html);
  
  $html = str_replace("%TIME_TYPE_OPTIONS%", getSelect($obj->timeMode, ",fixed,seconds,minutes,hours,days", "-- wählen --,Fester Zeitraum,Die letzten X Sekunden,Die letzten X Minuten,Die letzten X Stunden,Die letzten X Tage"), $html);
  
  $html = str_replace("%IMAGE%", "img/empty.gif", $html);
  
  if ($obj->timeMode=="fixed")
  {
    $obj->timeParam1 = date("d.m.Y H:i:s", $obj->timeParam1); 
    $obj->timeParam2 = date("d.m.Y H:i:s", $obj->timeParam2);
  }
  
  $html = str_replace("%TIME_PARAM_1%", $obj->timeParam1, $html);
  $html = str_replace("%TIME_PARAM_2%", $obj->timeParam2, $html);
  
  $html = str_replace("%DIST_VALUE%", $obj->distValue, $html);
  $html = str_replace("%DIST_TYPE_OPTIONS%", getSelect($obj->distType, ",s,m,h,d", "-- keiner --,Sekunden,Minuten,Stunden,Tage"), $html);
  
  $html = str_replace("%SUBMIT_TITLE%", "Änderungen speichern", $html);
  
  $html = str_replace("%GRAPH_TYPE%", $obj->type, $html);
  $html = str_replace("%HEIGHT_TYPE_OPTIONS%", getSelect("", ",percent,fixed", "Automatisch,Prozent der Fensterhöhe:,Feste Höhe in Pixel:"), $html);
  $html = str_replace("%TIME_TYPE%", $obj->timeMode, $html);
  
  chooseTag("%OPT_DELETE%", $html);
  chooseTag("%OPT_SIGNALS%",$html);
  
  $allFeatureInstances = readFeatureInstances();
  //$allFeatureClasses = readFeatureClasses();
  $allFeatureFunctions = readFeatureFunctions();
  
  $signals="";
  $signalTag = getTag("%SIGNALS%",$html);
  $signalEventsTag = getTag("%SIGNAL_EVENTS%",$signalTag);
  $signalFktTag = getTag("%SIGNAL_FKTS%",$signalTag);
    
  $erg = MYSQL_QUERY("select id,type,color,title,featureInstanceId,functionId,fkt from graphSignals where graphId='$id' order by id") or die(MYSQL_ERROR());
  while($obj=MYSQL_FETCH_OBJECT($erg))
  {
  	$actTag = $signalTag;
  	
    $signalEvents="";  
	  $signalFkts="";  
    $erg2 = MYSQL_QUERY("select id,featureInstanceId,functionId,fkt from graphSignalEvents where graphSignalsId='$obj->id' order by id") or die(MYSQL_ERROR());
    while($obj2=MYSQL_FETCH_OBJECT($erg2))    
    {
    	$actSignalFktTag = $signalFktTag;
    	if ($obj2->fkt=="") $obj2->fkt="[anlegen]";
	    $actSignalFktTag = str_replace("%FKT%",$obj2->fkt,$actSignalFktTag);
	        	
    	$actEventTag = $signalEventsTag;
    	
	    $roomName = getRoomForFeatureInstance($obj2->featureInstanceId)->name;
	    $actFeatureInstanceName = $allFeatureInstances[$obj2->featureInstanceId]->name;
	    //  $actClassName = $allFeatureClasses[$actFeatureInstance->featureClassesId]->name;
	    $functionName = $allFeatureFunctions[$obj2->functionId]->name;
	    $signalEvent=$roomName." » ".$actFeatureInstanceName." » ".$functionName;
	    $actEventTag = str_replace("%SIGNAL_EVENT%",$signalEvent,$actEventTag);
        $actEventTag = str_replace("%EVENT_ID%",$obj2->id,$actEventTag);
        $actSignalFktTag = str_replace("%EVENT_ID%",$obj2->id,$actSignalFktTag);
	    $signalEvents .= $actEventTag;
	    $signalFkts .= $actSignalFktTag;
    }  
	  
	  $actTag = str_replace("%GRAPH_TYPE_OPTIONS%", getSelect($obj->type, "line,spline,steps,scatter", "Line,Spline,Steps,Events"), $actTag);    
    $actTag = str_replace("%SIGNAL_EVENTS%",$signalEvents,$actTag); 
    $actTag = str_replace("%SIGNAL_FKTS%",$signalFkts,$actTag);
    $actTag = str_replace("%SIGNAL_ID%",$obj->id,$actTag);
    $actTag = str_replace("%ID%",$id,$actTag);
    $actTag = str_replace("%COLOR%",$obj->color,$actTag);
    if ($obj->title=="") $obj->title="[anlegen]";
    $actTag = str_replace("%SIGNAL_NAME%",$obj->title,$actTag);
    $signals.=$actTag;
  }
  
  $html = str_replace("%SIGNALS%",$signals,$html);
  
  $link = $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
  $link = substr($link,0,strrpos($link,"/"));
  $html = str_replace("%LINK%","http://".$link."/showGraph.php?id=$id",$html);
}
else
{
  $html = str_replace("%MODE%", "erstellen", $html);
  $html = str_replace("%NAME%", "", $html);
  $html = str_replace("%WIDTH%", "", $html);
  $html = str_replace("%HEIGHT%", "", $html);
  
    $html = str_replace("%THEME_OPTIONS%", getSelect("default", "default,dark-unica,sand-signika,grid-light", "Standard,Dunkel,Sand,Gitter"), $html);

  $html = str_replace("%GRAPH_TYPE_OPTIONS%", getSelect("", "line,spline,scatter", "Line,Spline,Events"), $html);
  $html = str_replace("%TIME_TYPE_OPTIONS%", getSelect("", ",fixed,seconds,minutes,hours,days", "-- wählen --,Fester Zeitraum,Die letzten X Sekunden,Die letzten X Minuten,Die letzten X Stunden,Die letzten X Tage"), $html);
  $html = str_replace("%IMAGE%", "img/empty.gif", $html);
  $html = str_replace("%TIME_PARAM_1%", "", $html);
  $html = str_replace("%TIME_PARAM_2%", "", $html);
  $html = str_replace("%DIST_VALUE%", "", $html);
  $html = str_replace("%DIST_TYPE_OPTIONS%", getSelect("", ",s,m,h,d", "-- keiner --,Sekunden,Minuten,Stunden,Tage"), $html);
  $html = str_replace("%SUBMIT_TITLE%", "Diagramm erstellen", $html);

  $html = str_replace("%GRAPH_TYPE%", "", $html);
  $html = str_replace("%TIME_TYPE%", "", $html);
  
  removeTag("%OPT_DELETE%", $html);
  removeTag("%OPT_SIGNALS%",$html);
}

$html = str_replace("%ERROR%", "", $html);
$html = str_replace("%ID%", $id, $html);

show();

//17.1.2011 20:31:57
function selectToTime($in)
{
  $in = trim($in);
  
  $pos = strpos($in, ".");
  $pos2 = strpos($in, ".", $pos + 1);
  $pos3 = strpos($in, ":");
  $pos4 = strpos($in, ":", $pos3 + 1);
  $pos5 = strpos($in, " ");
  if ($pos5 === FALSE)
    $pos = strlen($in);
  
  $day = substr($in, 0, $pos);
  $month = substr($in, $pos + 1, $pos2 - $pos - 1);
  $year = substr($in, $pos2 + 1, $pos5 - $pos2 - 1);
  
  if ($pos3 !== FALSE)
  {
    $hour = substr($in, $pos5 + 1, $pos3 - $pos5 - 1);
    $minute = substr($in, $pos3 + 1, $pos4 - $pos3 - 1);
    $second = substr($in, $pos4 + 1, strlen($in) - $pos4 - 1);
    return mktime($hour, $minute, $second, $month, $day, $year);
  }
  else
    return mktime(0, 0, 0, $month, $day, $year);
}
?>