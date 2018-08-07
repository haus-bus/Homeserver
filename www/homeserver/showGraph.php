<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($truncate==1) QUERY("truncate table graphData");

include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/generateGraphData.php");
generateGraphData();

$start = time();

$erg = QUERY("select * from graphs where id='$id' limit 1");
if ($graphObj = mysqli_fetch_OBJECT($erg)) {}
else die("ID $id nicht gefunden");

$erg = QUERY("select * from graphSignals where graphId='$id' order by id");
while($obj=mysqli_fetch_OBJECT($erg))
{
	 $signals[$obj->id]=$obj;
}
  
// fixed,seconds,minutes,hours,days
if ($graphObj->timeMode == "seconds") $and = "time>" . (time() - $graphObj->timeParam1);
else if ($graphObj->timeMode == "minutes") $and = "time>" . (time() - $graphObj->timeParam1 * 60);
else if ($graphObj->timeMode == "hours") $and = "time>" . (time() - $graphObj->timeParam1 * 3600);
else if ($graphObj->timeMode == "days") $and = "time>" . (time() - $graphObj->timeParam1 * 86400);
else
{
  if ($graphObj->timeParam1 > 0) $and = "time>" . $graphObj->timeParam1;
  if ($graphObj->timeParam2 > 0)
  {
    if ($and != "") $and .= " and ";
    $and = "time<" . $graphObj->timeParam2;
  }
}

$minDist = 0;
if ($graphObj->distType=="s") $minDist = $graphObj->distValue;
else if ($graphObj->distType=="m") $minDist = $graphObj->distValue*60;
else if ($graphObj->distType=="h") $minDist = $graphObj->distValue*3600;
else  if ($graphObj->distType=="d") $minDist = $graphObj->distValue*86400;

$sql = "select signalId,time,value from graphData where graphId='$id' and $and order by id";
$erg = QUERY($sql);
while ( $obj = mysqli_fetch_OBJECT($erg) )
{
	if ($lastTime[$obj->signalId]=="") $lastTime[$obj->signalId]=0;
	if ($lastTime[$obj->signalId] > 0 && $minDist > 0 && ($obj->time-$lastTime[$obj->signalId]) < $minDist ) continue;
		
 	if ($data[$obj->signalId]!="") $data[$obj->signalId].=",";
  $data[$obj->signalId].= "[".($obj->time*1000).",".$obj->value."]";
  $lastTime[$obj->signalId]=$obj->time;
}

$html = file_get_contents("templates/showGraph_design.html");

$html = str_replace("%TITLE%",$graphObj->title,$html);

if ($graphObj->heightMode=="percent") $height=",height: window.innerHeight/100*".$graphObj->height;
else if ($graphObj->heightMode=="fixed") $height=",height: ".$graphObj->height;


$html = str_replace("%height%",$height,$html);

$html = str_replace("%SUBTITLE%","",$html);
$html = str_replace("%X_AXIS_MIN_RANGE%","1",$html);
$html = str_replace("%Y_AXIS_TITLE%","",$html);

if ($graphObj->theme=="") $graphObj->theme="default";
$html = str_replace("%THEME%",$graphObj->theme,$html);

$seriesTag=getTag("%SERIES%",$html);
$series="";

foreach( $signals as $signalId=>$obj )
{
	$actTag = $seriesTag;
	$actTag = str_replace("%SIGNAL_NAME%",$obj->title,$actTag); 
	$actTag = str_replace("%SIGNAL_COLOR%",$obj->color,$actTag);
	if ($obj->type=="steps")
	{
		$actTag = str_replace("%SERIES_TYPE%","line",$actTag);
		$actTag = str_replace("%STEPS%","step: true,",$actTag);
	}
	else
	{
		$actTag = str_replace("%SERIES_TYPE%",$obj->type,$actTag);
		$actTag = str_replace("%STEPS%","",$actTag);
	}

	$actTag = str_replace("%data%",$data[$signalId],$actTag);
	
	if ($series!="") $series.=",";
	$series.=$actTag;
}
$html = str_replace("%SERIES%",$series,$html);

die($html);
?>

