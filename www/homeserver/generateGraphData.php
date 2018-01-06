<?php
include_once ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

function generateGraphData($graphId="")
{
	$erg = MYSQL_QUERY("select time from graphData where graphId='-1' limit 1") or die(MYSQL_ERROR());
  if($row=MYSQL_FETCH_ROW($erg)) $lastId=$row[0];
  else
  {
  	$lastId=0;
  	MYSQL_QUERY("INSERT into graphData (graphId, time) values('-1','0')") or die(MYSQL_ERROR());
  }
	
	if ($graphId>0) $whereGraph="WHERE graphs.id='$graphId'";

$erg = QUERY("select * from graphs");
while ($graphObj = MYSQL_FETCH_OBJECT($erg)) 
{
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
  $graphAnd[$graphObj->id]=$and;
}

  $where = "1=2 ";
  $sql = "SELECT graphsignals.id, graphsignals.type, graphsignals.color,graphsignals.title,
                featureInstances.objectId,
				        featureFunctions.functionId,
				        graphs.id as graphId,
				        graphSignalEvents.fkt,
				        graphSignalEvents.id as eventId
         				FROM graphsignals 
         				join graphSignalEvents on (graphsignals.id=graphSignalEvents.graphSignalsId)
         				join graphs on (graphs.id=graphsignals.graphId)
        				join featureInstances on ( featureInstances.id=graphSignalEvents.featureInstanceId  )
				        join featureFunctions on ( featureFunctions.id=graphSignalEvents.functionId  )
				        $whereGraph";
  $erg = QUERY($sql);
  while ( $obj = mysql_fetch_object($erg) )
  {
  	$signals[$obj->graphId][$obj->id][$obj->eventId] = $obj;
  	$where .= " or (senderObj='$obj->objectId' and fktId='$obj->functionId' and ".$graphAnd[$obj->graphId].")";
  }

  if ( count( $signals ) == 0 ) return;
  

  $myLastId=$lastId;
  $sql = "select functionData,senderObj,time,fktId,id from udpCommandLog where ($where) and id>'$lastId' order by id";
  //die($sql);
  $erg = QUERY($sql);
  while ( $obj = MYSQL_FETCH_OBJECT($erg) )
  {
  	$myLastId=$obj->id;
    foreach( $signals as $graphId=>$arr)
    {
 	   foreach( $arr as $signalId=>$arr2)
     {
	 	   foreach( $arr2 as $eventId=>$sigObj)
       {
        if ($sigObj->objectId == $obj->senderObj && $sigObj->functionId == $obj->fktId)
        {
          $fktData = unserialize($obj->functionData);
	        $fktParams = $fktData->paramData;
	        $actFkt = $sigObj->fkt;
	        foreach ( $fktParams as $actParam )
		      {
		         if (strpos($actFkt, $actParam->name) !== FALSE) $actFkt = str_replace($actParam->name, $actParam->dataValue, $actFkt);
		      }
		      $val = matheval($actFkt);
		    	
		    	//echo "Graph: ".$graphId.", Signal: $signalId".", time = ".$obj->time.", value = ".$val."<br>";
		    	MYSQL_QUERY("INSERT into graphData (graphId, signalId, time, value) values('$graphId','$signalId','$obj->time','$val')") or die(MYSQL_ERROR());
		    }
		  }
     }
    }
  }
  
  if ($myLastId>$lastId) MYSQL_QUERY("UPDATE graphData set time='$myLastId' where graphId='-1' limit 1") or die(MYSQL_ERROR());
}

function matheval($equation)
{
  $equation = preg_replace("/[^0-9+\-.*\/()%]/", "", $equation);
  // fix percentage calcul when percentage value < 10
  $equation = preg_replace("/([+-])([0-9]{1})(%)/", "*(1\$1.0\$2)", $equation);
  // calc percentage
  $equation = preg_replace("/([+-])([0-9]+)(%)/", "*(1\$1.\$2)", $equation);
  // you could use str_replace on this next line
  // if you really, really want to fine-tune this equation
  $equation = preg_replace("/([0-9]+)(%)/", ".\$1", $equation);
  if ($equation == "") $return = 0;
  else eval("\$return=" . $equation . ";");
  return $return;
}
?>

