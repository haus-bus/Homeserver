<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($checkUpdate!="")
{
  $erg = QUERY("select id from udpCommandLog where (type='EVENT' or type='RESULT') and id>'$checkUpdate' order by id limit 1");
  if ($row=mysqli_fetch_ROW($erg)) die("".($row[0]-1));
  else die("");
}

$html = loadTemplate("liveEvents_design.html");

if ($lastId=="")
{
  $erg = QUERY("select max(id) from udpCommandLog");
  if ($row=mysqli_fetch_ROW($erg)) $lastId=$row[0];
  else $lastId=0;
  $firstId=$lastId;
}

$entryTag=getTag("%ENTRY%",$html);
$entries="";
$c=0;
$erg = QUERY("select id,time,type,messageCounter,sender,receiver,function,params from udpCommandLog where id>'$firstId' order by id desc limit 50");
while($obj=mysqli_fetch_OBJECT($erg))
{
  if ($c==0) $lastId=$obj->id;
  $c++;
  $actTag = $entryTag;
  $actTag = str_replace("%ID%",$obj->id,$actTag);
  $actTag = str_replace("%TIME%",date("H:i:s",$obj->time),$actTag);
  $actTag = str_replace("%SENDER%",$obj->sender,$actTag);
  $actTag = str_replace("%FUNCTION%",$obj->function,$actTag);
  $actTag = str_replace("%PARAMS%",$obj->params,$actTag);
  $actTag = str_replace("%ID%",$obj->id,$actTag);
  
  if ($c%2==0) $actTag = str_replace("%BG%","#eeeeee",$actTag);
  else $actTag = str_replace("%BG%","#ffffff",$actTag);
  $entries.=$actTag;
}
$html = str_replace("%ENTRY%",$entries,$html);
$html = str_replace("%LASTID%",$lastId,$html);
$html = str_replace("%FIRSTID%",$firstId,$html);

die($html);

?>
