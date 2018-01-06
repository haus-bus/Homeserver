<?php
//Cronjob, der einmal pro Stunde aufgefunden wird
require_once($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/global.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/dbconnect.php");

$currentReaderClassesId = getClassesIdByName("CurrentReader");

$erg = MYSQL_QUERY("select objectId from featureInstances where featureClassesId='$currentReaderClassesId' order by id limit 1") or die(MYSQL_ERROR());
if($obj=MYSQL_FETCH_OBJECT($erg))
{
	 $objectId=$obj->objectId;
	 
	 $erg = MYSQL_QUERY("select time,functionData from udpCommandLog where senderObj='$objectId' and function='evCurrent' order by time desc limit 1") or die(MYSQL_ERROR());
	 if($obj=MYSQL_FETCH_OBJECT($erg))
	 {
  	 $data = unserialize($obj->functionData);
	   $endCurrent = $data->paramData[0]->dataValue;

     $min = time()-60*60*24;
     $sql = "select time,functionData from udpCommandLog where senderObj='$objectId' and function='evCurrent' and time>$min order by time limit 1";
     echo $sql."<br>";
  	 $erg = MYSQL_QUERY($sql) or die(MYSQL_ERROR());
	   if($obj=MYSQL_FETCH_OBJECT($erg))
	   {
	 	    $data = unserialize($obj->functionData);
	 	    $startCurrent = $data->paramData[0]->dataValue;
	 	  
	 	    $verbrauch = $endCurrent-$startCurrent;
	 	    $result = round($verbrauch/1000,2);
	 	    
	 	    echo $endCurrent." - ".$startCurrent." = ".$verbrauch."<br>";
	 	    
	 	    MYSQL_QUERY("UPDATE basicConfig set paramValue='$result' where paramKey='current1d' limit 1") or die(MYSQL_ERROR());
 	    
	 	    
	 	    $min = time()-60*60*24*7;
	 	    $sql="select time,functionData from udpCommandLog where senderObj='$objectId' and function='evCurrent' and time>$min order by time limit 1";
	 	    echo $sql."<br>";
      	$erg = MYSQL_QUERY($sql) or die(MYSQL_ERROR());
    	  if($obj=MYSQL_FETCH_OBJECT($erg))
    	  {
    	 	  $data = unserialize($obj->functionData);
    	 	  $startCurrent = $data->paramData[0]->dataValue;
    	 	  
    	 	  $verbrauch = $endCurrent-$startCurrent;
    	 	  $result = round($verbrauch/1000,2);
    	 	  
    	 	  echo $endCurrent." - ".$startCurrent." = ".$verbrauch."<br>";
    	 	  
  	 	    MYSQL_QUERY("UPDATE basicConfig set paramValue='$result' where paramKey='current7d' limit 1") or die(MYSQL_ERROR());
    	 	  
    	 	  $min = time()-60*60*24*30;
    	 	  $sql="select time,functionData from udpCommandLog where senderObj='$objectId' and function='evCurrent' and time>$min order by time limit 1";
    	 	  echo $sql."<br>";
      	  $erg = MYSQL_QUERY($sql) or die(MYSQL_ERROR());
    	    if($obj=MYSQL_FETCH_OBJECT($erg))
    	    {
      	 	  $data = unserialize($obj->functionData);
    	 	    $startCurrent = $data->paramData[0]->dataValue;
    	 	  
    	 	    $verbrauch = $endCurrent-$startCurrent;
    	 	    $result = round($verbrauch/1000,2);
    	 	    echo $endCurrent." - ".$startCurrent." = ".$verbrauch."<br>";
    	 	    MYSQL_QUERY("UPDATE basicConfig set paramValue='$result' where paramKey='current30d' limit 1") or die(MYSQL_ERROR());
    	    }
    	  }
	   }
	 }
}


include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/generateGraphData.php");
generateGraphData();

?>