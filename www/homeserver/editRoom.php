<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted!="")
{
  if ($delete==1 && $id>0)
  {
    deleteRoom($id);
    triggerTreeUpdate();
    header("Location: editRoom.php");
    exit;
  }
  else
  {
    $orig=$_FILES['userfile']['name'];
    if ($orig!="")
    {
      $suffix=substr($orig,strrpos($orig,"."),strlen($orig)-strrpos($orig,"."));
      $picture = time().$suffix;
      move_uploaded_file ( $_FILES['userfile']['tmp_name'], "userpics/".$picture);
    }

    if($id=="")
    {
      QUERY("INSERT into rooms (name,picture) values('$name','$picture')");
      $id = query_insert_id();
      header("Location: editRoom.php?id=$id");
      exit;
    }
    else
    {
      if ($picture!="") $pic=",picture='$picture'";

      QUERY("UPDATE rooms set name='$name'$pic where id='$id' limit 1");
      $message="Einstellungen gespeichert";
    }
  }
}

setupTreeAndContent("editRoom_design.html");

if ($id=="")
{
  $html = str_replace("%ID%","", $html);
  $html = str_replace("%TITLE%","Neuen Raum anlegen", $html);
  $html = str_replace("%SUBMIT_TITLE%","Raum erstellen", $html);
  $html = str_replace("%ROOM_NAME%","", $html);
  removeTag("%IMAGE%",$html);
  removeTag("%FEATURES%",$html);
}
else
{
  $html = str_replace("%ID%",$id, $html);
  $html = str_replace("%TITLE%","Raum bearbeiten", $html);
  $html = str_replace("%SUBMIT_TITLE%","Ändern", $html);
  chooseTag("%FEATURES%",$html);

  $erg = QUERY("select id, name,picture from rooms where id='$id' limit 1");
  if ($obj=mysqli_fetch_OBJECT($erg))
  {
    $html = str_replace("%ROOM_NAME%",$obj->name, $html);
    if ($obj->picture=="") removeTag("%IMAGE%",$html);
    else $html = str_replace("%IMAGE%","userpics/".$obj->picture,$html);

    $where="1=2 ";
    $erg = QUERY("select featureInstanceId from roomFeatures where roomId='$id'");
    while($obj=mysqli_fetch_OBJECT($erg))
    {
      $where.=" or id='$obj->featureInstanceId'";
    }

    $allControllers = readControllers();
    $allFeatureClasses = readFeatureClasses();
    $controllerTag = getTag("%CONTROLLER%",$html);
    $featuresTag = getTag("%CONTROLLER_FEATURE%",$controllerTag);
    $content="";
    $last="";
    $erg = QUERY("select id,controllerId, featureClassesId,name from featureInstances where $where order by controllerId,featureClassesId,name");
    while($obj=mysqli_fetch_OBJECT($erg))
    {
      $actController = $allControllers[$obj->controllerId];
       
      $actFeature = $allFeatureClasses[$obj->featureClassesId];
       
      if ($actController->id!=$last)
      {
        if ($last!="")
        {
          $actTag = str_replace("%CONTROLLER_FEATURE%",$actFeatures,$actTag);
          $content.=$actTag;
        }

        $actTag = $controllerTag;
        $status = "img/online2.gif";
        if ($actController->online=="0") $status = "img/offline2.gif";
        $actTag = str_replace("%STATUS%",$status, $actTag);
        $actTag = str_replace("%CONTROLLER_NAME%",$actController->name, $actTag);
        $actFeatures="";
      }
      $last=$actController->id;
      $actFeatureTag = $featuresTag;
      $actFeatureTag = str_replace("%FEATURE%",$actFeature->name.": ".$obj->name, $actFeatureTag);
      $actFeatures.=$actFeatureTag;
    }
    $actTag = str_replace("%CONTROLLER_FEATURE%",$actFeatures,$actTag);
    $content.=$actTag;
    $html = str_replace("%CONTROLLER%",$content,$html);
  }
  else die("FEHLER! Ungültige ID $id");
}


show();

?>
