<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($submitted == 1)
{
  $data["startupDelay"] = 0;
  $data["IP_0"] = 192;
  $data["IP_1"] = 168;
  $data["IP_2"] = 2;
  $data["IP_3"] = 200;
  
  $erg = MYSQL_QUERY("select objectId from controller where id='$id' limit 1") or die(MYSQL_ERROR());
  if ($row = MYSQL_FETCH_ROW($erg))
  {
    $oldObjectId = $row[0];
    $oldDeviceId = getDeviceId($oldObjectId);
    $data["deviceId"] = $oldDeviceId;
    
    $erg = QUERY("select functionData from lastReceived where function='Configuration' and senderObj='$oldObjectId' order by id desc limit 1");
    if ($row = MYSQL_FETCH_ROW($erg))
    {
      $config = unserialize($row[0])->paramData;
      foreach ( $config as $obj )
      {
        $data[$obj->name] = $obj->dataValue;
      }
    }
  }
  else
    die("Altern controller $id nicht gefunden");
  
  
  $erg = MYSQL_QUERY("select objectId from controller where id='$newController' limit 1") or die(MYSQL_ERROR());
  if ($row = MYSQL_FETCH_ROW($erg))
  {
    $newObjectId = $row[0];
    
    callObjectMethodByName($newObjectId, "setConfiguration", $data);
    sleep(1);
    callObjectMethodByName($newObjectId, "reset");
    
    deleteController($newController);
    flushIt();
    sleep(3);
    updateControllerStatus();
    echo "<script>location='editController.php?id=$id';</script>";
    exit();
  }
  else
    die("Controller $newController nicht gefunden");
}

setupTreeAndContent("restoreController_design.html", $message);

$html = str_replace("%ID%", $id, $html);

$allController = readControllers();

$options = "";
foreach ( $allController as $obj )
{
  if ($obj->id == $id)
  {
    $objectId = $obj->objectId;
    $html = str_replace("%CONTROLLER_NAME%", $obj->name, $html);
    continue;
  }
  
  if ($obj->online != 1)
    continue;
  
  $options .= "<option value='$obj->id'>$obj->name";
}

$html = str_replace("%CONTROLLER_OPTIONS%", $options, $html);
if ($objectId == "")
  die("FEHLER! Ungültige ID $id");

show();

?>