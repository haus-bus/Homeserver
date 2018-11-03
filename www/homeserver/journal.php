<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($nrElements=="") $nrElements=400;

if ($action=="delete")
{
  if ($confirm==1) 
  {
  	QUERY("TRUNCATE table udpCommandLog");
  	QUERY("TRUNCATE table udpdatalog");
  }
  else showMessage("Soll das Journal wirklich gelöscht werden?","Journal löschen","journal.php?action=delete&confirm=1","Ja, löschen","journal.php","Nein, zurück");
}

$order="desc";
if ($submitted!="")
{ 
  $where="where 1=1";
  if ($von!="")
  {
     $vonTime = selectToTime($von);
     $where.=" and udpCommandLog.time>='$vonTime'";
  }
  if ($bis!="")
  {
     $bisTime = selectToTime($bis);
     $where.=" and udpCommandLog.time<='$bisTime'";
  }
  if ($type!="ALLES") $where.=" and type='$type'";
  if ($type!="sender" && $sender!="") $where.=" and sender like '%$sender%'";
  if ($type!="receiver" && $receiver!="") $where.=" and receiver like '%$receiver%'";
  if ($type!="function" && $function!="") $where.=" and function = '$function'";
  if ($senderRaum!="") $where.=" and sender like 'Raum: $senderRaum -%'";
  if ($receiverRaum!="") $where.=" and receiver like 'Raum: $receiverRaum -%'";
  if ($noErrors==1) $where.=" and function!='evError'";
  
  if ($von!="" && $bis=="") $order="asc";
}

setupTreeAndContent("journal_design.html");

if ($submitted=="Exportieren")
{
	$erg = QUERY("select udpCommandLog.*,data from udpCommandLog join udpdatalog on (udpCommandLog.udpDataLogId=udpdatalog.id) $where order by id desc limit $nrElements");
  while($obj=mysqli_fetch_OBJECT($erg))
  {
  	 $export.=serialize($obj)."XYZ";
  }
  
	$size = strlen($export);
  header("Content-type: text/plain");
  header("Content-disposition: attachment; filename=export_".date("d.m.Y-H_i_s").".bus");
  header("Content-Length: ".$size);
  header("Pragma: no-cache");
  header("Expires: 0");
  die($export);
}

$functionOptions="";
if ($function=="") $selected="selected"; else $selected="";
$functionOptions.="<option value='' $selected>";


$erg = QUERY("select distinct(name) from featureFunctions order by name");
while($row=mysqli_fetch_ROW($erg))
{
	 $act = $row[0];
	 
	 if ($function==$act) $selected="selected"; else $selected="";
	 $functionOptions.="<option value='$act' $selected>$act";
}

/*$erg = QUERY("select distinct(function) from udpCommandLog order by function");
while($row=mysqli_fetch_ROW($erg))
{
	 $act = $row[0];
	 
	 if ($function==$act) $selected="selected"; else $selected="";
	 $functionOptions.="<option value='$act' $selected>$act";
}*/
$html = str_replace("%FUNCTION_OPTIONS%",$functionOptions,$html);


if ($senderRaum=="") $selected="selected"; else $selected="";
$senderRaumOptions="<option value='' $selected>";
	 
if ($receiverRaum=="") $selected="selected"; else $selected="";
$receiverRaumOptions.="<option value='' $selected>";
$erg = QUERY("select distinct(name) from rooms order by name");
while($row=mysqli_fetch_ROW($erg))
{
	 $act = $row[0];
	 
	 if ($senderRaum==$act) $selected="selected"; else $selected="";
	 $senderRaumOptions.="<option value='$act' $selected>$act";
	 
	 if ($receiverRaum==$act) $selected="selected"; else $selected="";
	 $receiverRaumOptions.="<option value='$act' $selected>$act";
}
$html = str_replace("%SENDER_RAUM_OPTIONS%",$senderRaumOptions,$html);
$html = str_replace("%RECEIVER_RAUM_OPTIONS%",$receiverRaumOptions,$html);


if ($noErrors==1) $noErrorsChecked="checked";
$html = str_replace("%NO_ERRORS_CHECKED%",$noErrorsChecked,$html);

$html = str_replace("%NR_ELEMENTS%",$nrElements,$html);

$entryTag=getTag("%ENTRY%",$html);
$entries="";
$c=0;
$export="";

$sql = "select id,time,type,messageCounter,sender,receiver,function,params from udpCommandLog $where order by id $order limit $nrElements";
//echo $sql."<br>";
$erg = QUERY($sql);
while($obj=mysqli_fetch_OBJECT($erg))
{
  $c++;
  $actTag = $entryTag;
  $actTag = str_replace("%ID%",$obj->id,$actTag);
  $actTag = str_replace("%TIME%",date("d.m.y H:i:s",$obj->time),$actTag);
  $actTag = str_replace("%TYP%",$obj->type,$actTag);
  $actTag = str_replace("%SENDER%",$obj->sender,$actTag);
  $actTag = str_replace("%RECEIVER%",$obj->receiver,$actTag);
  $actTag = str_replace("%FUNCTION%",$obj->function,$actTag);
  $actTag = str_replace("%PARAMS%",$obj->params,$actTag);
  $actTag = str_replace("%ID%",$obj->id,$actTag);
  
  if ($c%2==0) $actTag = str_replace("%BG%","#eeeeee",$actTag);
  else $actTag = str_replace("%BG%","#ffffff",$actTag);
  if ($order=="asc") $entries=$actTag.$entries;
  else $entries.=$actTag;
}
$html = str_replace("%ENTRY%",$entries,$html);

$html = str_replace("%VON%",$von,$html);
$html = str_replace("%BIS%",$bis,$html);
$typeOptions = getSelect($type,"ALLES,EVENT,ACTION,FUNCTION,RESULT");
$html = str_replace("%TYP_OPTIONS%",$typeOptions,$html);
$html = str_replace("%SENDER%",$sender,$html);
$html = str_replace("%RECEIVER%",$receiver,$html);
$html = str_replace("%FUNCTION%",$function,$html);

$html = str_replace("%CLIP1%","<div style=\"width:200px;overflow-x: hidden;\" onclick=\"if (this.style.width=='200px') this.style.width='1000px'; else this.style.width='200px'\">",$html);
$html = str_replace("%CLIP2%","<div style=\"width:250px;overflow-x: hidden;direction: rtl;\" onclick=\"if (this.style.width=='250px') {this.style.width='1000px';this.style.direction='ltr';} else {this.style.width='250px';this.style.direction='rtl';}\">",$html);


show();

//17.1.2011 20:31:57
function selectToTime($in)
{
   $in=trim($in);
   
   $pos=strpos($in,".");
   $pos2=strpos($in,".",$pos+1);
   $pos3=strpos($in,":");
   $pos4=strpos($in,":",$pos3+1);
   $pos5=strpos($in," ");
   if ($pos5===FALSE) $pos=strlen($in);
   
   $day = substr($in,0,$pos);
   $month = substr($in,$pos+1,$pos2-$pos-1);
   $year =  substr($in,$pos2+1,$pos5-$pos2-1);
   
   if ($pos3!==FALSE)
   {
      $hour = substr($in,$pos5+1,$pos3-$pos5-1);
      $minute = substr($in,$pos3+1,$pos4-$pos3-1);
      $second = substr($in,$pos4+1,strlen($in)-$pos4-1);
      return mktime ($hour, $minute, $second, $month, $day, $year); 
   }
   else return mktime (0, 0, 0, $month, $day, $year);
  
}

?>
