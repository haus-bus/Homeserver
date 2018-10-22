<?php
//Cronjob, der einmal pro Stunde aufgefunden wird
require_once($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/global.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/dbconnect.php");

$currentReaderClassesId = getClassesIdByName("CurrentReader");

$erg = QUERY("select objectId from featureInstances where featureClassesId='$currentReaderClassesId' order by id limit 1");
if($obj=mysqli_fetch_OBJECT($erg))
{
	 $objectId=$obj->objectId;
	 
	 $sql = "select time,functionData from udpCommandLog where senderObj='$objectId' and function='evCurrent' order by time desc limit 1";
	 //echo $sql."<br>";
	 $erg = QUERY($sql);
	 if($obj=mysqli_fetch_OBJECT($erg))
	 {
  	 $data = unserialize($obj->functionData);
	   $endCurrent = $data->paramData[0]->dataValue;

     $min = time()-60*60*24;
     $sql = "select time,functionData from udpCommandLog where senderObj='$objectId' and function='evCurrent' and time>$min order by time limit 1";
     //echo $sql."<br>";
  	 $erg = QUERY($sql);
	   if($obj=mysqli_fetch_OBJECT($erg))
	   {
	 	    $data = unserialize($obj->functionData);
	 	    $startCurrent = $data->paramData[0]->dataValue;
	 	  
	 	    $verbrauch = $endCurrent-$startCurrent;
	 	    $result = round($verbrauch/1000,2);
	 	    
	 	    echo $endCurrent." - ".$startCurrent." = ".$verbrauch."<br>";
	 	    
	 	    QUERY("UPDATE basicConfig set paramValue='$result' where paramKey='current1d' limit 1");
 	    
	 	    
	 	    $min = time()-60*60*24*7;
	 	    $sql="select time,functionData from udpCommandLog where senderObj='$objectId' and function='evCurrent' and time>$min order by time limit 1";
	 	    echo $sql."<br>";
      	$erg = QUERY($sql);
    	  if($obj=mysqli_fetch_OBJECT($erg))
    	  {
    	 	  $data = unserialize($obj->functionData);
    	 	  $startCurrent = $data->paramData[0]->dataValue;
    	 	  
    	 	  $verbrauch = $endCurrent-$startCurrent;
    	 	  $result = round($verbrauch/1000,2);
    	 	  
    	 	  echo $endCurrent." - ".$startCurrent." = ".$verbrauch."<br>";
    	 	  
  	 	    QUERY("UPDATE basicConfig set paramValue='$result' where paramKey='current7d' limit 1");
    	 	  
    	 	  $min = time()-60*60*24*30;
    	 	  $sql="select time,functionData from udpCommandLog where senderObj='$objectId' and function='evCurrent' and time>$min order by time limit 1";
    	 	  echo $sql."<br>";
      	  $erg = QUERY($sql);
    	    if($obj=mysqli_fetch_OBJECT($erg))
    	    {
      	 	  $data = unserialize($obj->functionData);
    	 	    $startCurrent = $data->paramData[0]->dataValue;
    	 	  
    	 	    $verbrauch = $endCurrent-$startCurrent;
    	 	    $result = round($verbrauch/1000,2);
    	 	    echo $endCurrent." - ".$startCurrent." = ".$verbrauch."<br>";
    	 	    QUERY("UPDATE basicConfig set paramValue='$result' where paramKey='current30d' limit 1");
    	    }
    	  }
	   }
	 }
}


include_once($_SERVER["DOCUMENT_ROOT"] . "/homeserver/generateGraphData.php");
generateGraphData();

?>