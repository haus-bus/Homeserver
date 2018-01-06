<?php
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($action=="status")
{
	if (changesSince($lastStatusId))
	{
    callInstanceMethodByName($featureInstanceId, "getStatus");
    $result = waitForInstanceResultByName($featureInstanceId, 2, "Status", $lastLogId);
    updateLastLogId();
    die($lastLogId."#".getResultDataValueByName("celsius", $result).".".getResultDataValueByName("centiCelsius", $result));
  }
  exit;
}

$erg = MYSQL_QUERY("select featureClassesId from featureInstances where id='$featureInstanceId' limit 1") or die(MYSQL_ERROR());
if ($obj=MYSQL_FETCH_OBJECT($erg))
{
  $featureClassesId = $obj->featureClassesId;
}
else die("FEHLER! Ungültige featureInstanceId $featureInstanceId");

$html = loadTemplate("tempControl_design.html");
$html = str_replace("%FEATURE_INSTANCE_ID%",$featureInstanceId,$html);

$html = str_replace("%INITIAL_STATUS_ID%",updateLastLogId(),$html);


callInstanceMethodByName($featureInstanceId, "getStatus");
$result = waitForInstanceResultByName($featureInstanceId, 1, "Status", $lastLogId, "funtionDataParams", 0);
$status=$result[0]->dataValue.".".$result[1]->dataValue;
$html = str_replace("%TEMP%",$status,$html);

echo $html;

?>