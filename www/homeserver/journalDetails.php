<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

$html=loadTemplate("journalDetails_design.html");

$groupTag=getTag("%GROUP%",$html);
$groups="";
if ($import==1) $table="udpcommandlogimport";
else $table="udpcommandlog";
$erg = MYSQL_QUERY("select functionData,senderSubscriberData,receiverSubscriberData,udpDataLogId from $table where id='$id' limit 1") or die(MYSQL_ERROR());
if($obj=MYSQL_FETCH_OBJECT($erg))
{
	if ($import==1) $table="udpDataLogimport";
  else $table="udpDataLog";
  $erg2 = MYSQL_QUERY("select data from $table where id='$obj->udpDataLogId' limit 1") or die(MYSQL_ERROR());
  if($row=MYSQL_FETCH_ROW($erg2))
  {
    $data=explode(",",$row[0]);

    // UDP-Header (Ist es ein Paket von unseren Busteilnehmern ?)
    $headerOk=true;
    $dataPos=0;
    $i=0;
    $dataComment="Header: ";
    foreach ($UDP_HEADER_BYTES as $value)
    {
      if ($data[$i]!="0x".dechex($value)) $headerOk=false;
      $dataComment.="0x".dechex($value)." ";
      $i++;
      $dataPos++;
    }
    if (!$headerOk) $dataComment="Header nicht von uns: ".$data;
    else
    {
      $dataComment.="<br>Kontrollbyte: ".$data[$dataPos++];
      $dataComment.="<br>Nachrichtenzähler: ".$data[$dataPos++];
      $dataComment.="<br>Sender ObjectId: 0x".twoDigits(str_replace("0x","",$data[$dataPos+3])).twoDigits(str_replace("0x","",$data[$dataPos+2])).twoDigits(str_replace("0x","",$data[$dataPos+1])).twoDigits(str_replace("0x","",$data[$dataPos]));
      $dataPos+=4;
      $dataComment.="<br>Empfänger ObjectId: 0x".twoDigits(str_replace("0x","",$data[$dataPos+3])).twoDigits(str_replace("0x","",$data[$dataPos+2])).twoDigits(str_replace("0x","",$data[$dataPos+1])).twoDigits(str_replace("0x","",$data[$dataPos]));
      $dataPos+=4;
      $dataComment.="<br>Nutzdatenlänge: ".(hexdec($data[$dataPos++])+hexdec($data[$dataPos++])*256);
      $dataComment.="<br>Function ID: ".$data[$dataPos++];
      
      $pos=0;
      for ($i=0;$i<$dataPos;$i++)
      {
        $pos = strpos($row[0],",",$pos+1); 
      }
      $dataComment.="<br>Parameter: ".substr($row[0],$pos+1);
    }

    $html = str_replace("%DATA%",$row[0]."<br><br>".$dataComment,$html);
  }
  else $html = str_replace("%DATA%","unbekannte udpDataLogId ".$obj->udpDataLogId,$html);

  $actTag = $groupTag;
  $actTag = str_replace("%GROUP_NAME%","Sender Busteilnehmerdaten",$actTag);

  $myObj = unserialize($obj->senderSubscriberData);
  $entryTrag = getTag("%ENTRY%",$actTag);
  $entries="";
  foreach ($myObj as $key => $value)
  {
    $actEntryTag = $entryTrag;
    $actEntryTag = str_replace("%KEY%",$key,$actEntryTag);
    $value=convertValue($value);
    $actEntryTag = str_replace("%VALUE%",$value,$actEntryTag);
    $entries.=$actEntryTag;
  }
  $actTag = str_replace("%ENTRY%",$entries,$actTag);

  $groups.=$actTag;

  $actTag = $groupTag;
  $actTag = str_replace("%GROUP_NAME%","Empfänger Busteilnehmerdaten",$actTag);

  $myObj = unserialize($obj->receiverSubscriberData);
  $entryTrag = getTag("%ENTRY%",$actTag);
  $entries="";
  foreach ($myObj as $key => $value)
  {
    $actEntryTag = $entryTrag;
    $actEntryTag = str_replace("%KEY%",$key,$actEntryTag);
    $value=convertValue($value);
    $actEntryTag = str_replace("%VALUE%",$value,$actEntryTag);
    $entries.=$actEntryTag;
  }
  $actTag = str_replace("%ENTRY%",$entries,$actTag);

  $groups.=$actTag;


  $actTag = $groupTag;
  $actTag = str_replace("%GROUP_NAME%","Funktionsdaten",$actTag);

  $myObj = unserialize($obj->functionData);
  $entryTrag = getTag("%ENTRY%",$actTag);
  $entries="";
  foreach ($myObj as $key => $value)
  {
    $actEntryTag = $entryTrag;
    $actEntryTag = str_replace("%KEY%",$key,$actEntryTag);
    $value=convertValue($value);
    $actEntryTag = str_replace("%VALUE%",$value,$actEntryTag);
    $entries.=$actEntryTag;
  }
  $actTag = str_replace("%ENTRY%",$entries,$actTag);

  $groups.=$actTag;

}
else die("Fehler, unbekannte ID $id");

$html = str_replace("%GROUP%",$groups,$html);

die($html);


function convertValue($value)
{
  if (gettype($value)=="object" || gettype($value)=="array")
  {
    $myValue="<table border=1>";
    foreach ($value as $key => $actValue)
    {
      if (gettype($actValue)=="object" || gettype($actValue)=="array") $actValue=convertValue($actValue);
      $myValue.="<tr><td valign='top'>$key</td><td valign='top'>$actValue</td></tr>";
    }
    $myValue.="</table>";
    $value=$myValue;
  }
  return $value;
}

?>
