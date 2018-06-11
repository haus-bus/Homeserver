<?php
// Register Globals FIX
foreach (array('_GET', '_POST', '_COOKIE', '_SERVER') as $_SG) 
{
    foreach ($$_SG as $_SGK => $_SGV) 
    {
        $$_SGK = $_SGV;
    }
}
include("include/dbconnect.php");

$nextCheck = "60";
$nextCheck = "30";
$message="";
$lastId="-";

if ($id=="")
{
	$erg = QUERY("select id from appMessages order by id desc limit 1");
	if ($row=MYSQL_FETCH_ROW($erg)) $lastId=$row[0];
	else $lastId="0";
}
else
{
  $erg = QUERY("select id,title,message from appMessages where id>'$id' limit 1");
  if ($row=MYSQL_FETCH_ROW($erg))
  {
	   $lastId=$row[0];
	   $message=utf8_encode($row[2]);
  }
}

$result = $nextCheck."#".$lastId."#".$message;

file_put_contents("appNotification.log",date("d.m.y H:i:s").": ".$_SERVER['REMOTE_ADDR']." lastId $id -> ".$result." / jobId = ".$jobId."\n", FILE_APPEND);

die($result);
?>