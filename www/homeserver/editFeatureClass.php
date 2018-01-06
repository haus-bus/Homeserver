<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted!="")
{
  if($id=="")
  {
    MYSQL_QUERY("INSERT into featureClasses (name,classId,guiControl,smoketest,view) values('$name','$classId','$guiControl','$smoketest','$view')") or die(MYSQL_ERROR());
    $id = mysql_insert_id();
    header("Location: editFeatureClass.php?id=$id");
    exit;
  }
  else
  {
    if ($delete==1)
    {
      deleteFeatureClass($id);
      header("Location: editFeatureClass.php");
      exit;
    }
    else MYSQL_QUERY("UPDATE featureClasses set name='$name',classId='$classId',guiControl='$guiControl',smoketest='$smoketest',view='$view' where id='$id' limit 1") or die(MYSQL_ERROR());
    $message="Einstellungen gespeichert";
  }
}

setupTreeAndContent("editFeatureClass_design.html", $message);

if ($id=="")
{
  $html = str_replace("%ID%","", $html);
  $html = str_replace("%TITLE%","Neue Featureklasse anlegen", $html);
  $html = str_replace("%SUBMIT_TITLE%","Feature erstellen", $html);
  $html = str_replace("%FEATURE_NAME%","", $html);
  $html = str_replace("%CLASS_ID%","", $html);
  $html = str_replace("%GUI_CONTROL%","", $html);
  $html = str_replace("%GUI_CONTROL_FUNCTIONS%","", $html);
  $html = str_replace("%SMOKETEST%","", $html);
  removeTag("%ENTRIES%",$html);
  removeTag("%DELETE%",$html);
  $ansicht="Standard";
}
else
{
  $html = str_replace("%ID%",$id, $html);
  $html = str_replace("%TITLE%","Featureklasse bearbeiten", $html);
  $html = str_replace("%SUBMIT_TITLE%","Ändern", $html);
  chooseTag("%ENTRIES%",$html);
  chooseTag("%DELETE%",$html);

  $allFeatureClasses = readFeatureClasses();
  $allFeatureFunctions = readFeatureFunctions();
  $allFeatureFunctionParams = readFeatureFunctionParams();
  
  foreach($allFeatureClasses as $obj)
  {
  	if ($obj->id==$id)
  	{
      $html = str_replace("%FEATURE_NAME%",$obj->name, $html);
      $html = str_replace("%CLASS_ID%",$obj->classId, $html);
      $html = str_replace("%GUI_CONTROL%",$obj->guiControl, $html);
      $html = str_replace("%SMOKETEST%",$obj->smoketest, $html);
      $ansicht=$obj->view;
      break;
    }
  }

  $functionTag = getTag("%FUNCTION%",$html);
  $functions="";
  $maxParams=0;
  foreach($allFeatureFunctions as $obj)
  {
    if ($obj->featureClassesId==$id)
    {
      $actTag = $functionTag;
      $actTag = str_replace("%FEATURE_FUNCTION_ID%",$obj->id, $actTag);
      $actTag = str_replace("%TYPE%",$obj->type, $actTag);
      $actTag = str_replace("%NAME%",$obj->name, $actTag);
      $actTag = str_replace("%FUNCTION_ID%",$obj->functionId, $actTag);

      $paramTag = getTag("%PARAM%",$actTag);
      $params="";
      $actParamCount=0;
      foreach($allFeatureFunctionParams as $obj2)
      {
      	if ($obj2->featureFunctionId==$obj->id)
        {
          $actParamCount++;
          $actParamsTag = $paramTag;
          $actParamsTag = str_replace("%PARAM%",$obj2->name,$actParamsTag);
          $params.=$actParamsTag;
        }
      }

      if ($actParamCount>$maxParams)
      $maxParams=$actParamCount;
      $actTag = str_replace("%PARAM%",$params, $actTag);
      $functions.=$actTag;

      $paramTitleTag = getTag("%PARAM_TITLE%",$html);
      $paramTitles="";
      for ($i=0;$i<$maxParams;$i++)
      {
        $actTag = $paramTitleTag;
        $actTag = str_replace("%ACT_PARAM_TITLE%","Parameter".($i+1), $actTag);
        $paramTitles.=$actTag;
      }
      $html = str_replace("%PARAM_TITLE%",$paramTitles, $html);
    }
  }
  $html = str_replace("%FUNCTION%",$functions, $html);
}

if ($ansicht=="Standard")
{
  $html = str_replace("%standardChecked%","checked",$html);
  $html = str_replace("%experteChecked%","",$html);
  $html = str_replace("%entwicklerChecked%","",$html);
}
else  if ($ansicht=="Experte")
{
  $html = str_replace("%standardChecked%","",$html);
  $html = str_replace("%experteChecked%","checked",$html);
  $html = str_replace("%entwicklerChecked%","",$html);
}
else  if ($ansicht=="Entwickler")
{
  $html = str_replace("%standardChecked%","",$html);
  $html = str_replace("%experteChecked%","",$html);
  $html = str_replace("%entwicklerChecked%","checked",$html);
}

show();

?>
