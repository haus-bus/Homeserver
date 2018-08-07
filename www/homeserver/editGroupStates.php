<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted!="")
{
  $parts = explode(",",$paramIds);
  foreach ((array)$parts as $actId)
  {
    $name="stateName$actId";
    $name=$$name;

    if ($name=="")
    {
      deleteGroupState($actId);
    }
    else
    {
      $erg = QUERY("select id from groupStates where id='$actId' limit 1");
      if ($row=mysqli_fetch_ROW($erg))
      {
        QUERY("UPDATE groupStates set name='$name' where id='$actId' limit 1");
      }
      else
      {
        QUERY("INSERT into groupStates (groupId, name) values('$groupId','$name')");
      }
    }
  }
  header("Location: editGroupStates.php?groupId=$groupId&message=".urlencode("Die Änderungen wurden gespeichert"));
  exit;

}

setupTreeAndContent("editGroupStates_design.html", $message);

$html = str_replace("%GROUP_ID%",$groupId, $html);

$entriesTag = getTag("%ENTRIES%",$html);
$paramIds="";
$entries="";
$i=0;
$erg = QUERY("select id, name from groupStates where groupId='$groupId' and basics='0' order by id");
while($obj=mysqli_fetch_OBJECT($erg))
{
  $i++;
  $actTag = $entriesTag;
  $actTag = str_replace("%STATE_ID%",$obj->id, $actTag);
  $actTag = str_replace("%NAME%",$obj->name, $actTag);
  $actTag = str_replace("%I%",$i, $actTag);
  $entries.=$actTag;

  if ($paramIds!="") $paramIds.=",";
  $paramIds.=$obj->id;
}

$erg = QUERY("select max(id) from groupStates");
if ($row=mysqli_fetch_ROW($erg)) $nextId=$row[0]+1;
else $nextId=1;

if ($paramIds!="") $paramIds.=",";
$paramIds.=$nextId;

$actTag = $entriesTag;
$actTag = str_replace("%STATE_ID%",$nextId, $actTag);
$actTag = str_replace("%NAME%","", $actTag);
$i++;
$actTag = str_replace("%I%",$i, $actTag);
$entries.=$actTag;

$html = str_replace("%ENTRIES%",$entries, $html);
$html = str_replace("%PARAM_IDS%",$paramIds, $html);

show();

?>
