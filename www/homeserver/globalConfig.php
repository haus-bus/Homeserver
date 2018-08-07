<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

$controllers = readControllers();
$featureFunctionId=getClassesIdFunctionsIdByName($id, "setConfiguration");

if ($submitted!="")
{
  $erg = QUERY("select id,name from featureFunctionParams where featureFunctionId='$featureFunctionId' order by id");
  while($obj=mysqli_fetch_OBJECT($erg))
  {
    if ($obj->type=="WEEKTIME")
    {
      $value="param".$obj->id."Day";
      $day=$$value;
      $value="param".$obj->id."Hour";
      $hour=$$value;
      $value="param".$obj->id."Minute";
      $minute=$$value;
      $value=toWeekTime($day,$hour,$minute);
      $paramData[trim($obj->name)]=$value;
    }
    else
    {
      $param="param".$obj->id;
      $paramData[trim($obj->name)]=$$param;
    }
  }
  
  /*$erg = QUERY("select classId from featureClasses where id='$id' limit 1");
  $row=mysqli_fetch_ROW($erg);
  $myClassId=$row[0];
  $broadCastWithClassId = getObjectId(0, $myClassId, 0);
  callInstanceMethodForObjectId($broadCastWithClassId, $featureFunctionId, $paramData);
  */
  
  $erg = QUERY("select id, controllerId, objectId from featureInstances where featureClassesId='$id' order by name");
  while($obj=mysqli_fetch_OBJECT($erg))
  {
    if ($controllers[$obj->controllerId]->online!=1) continue;
    $actFeature = "instance".$obj->id;
    if ($$actFeature==1)
    {
      callInstanceMethodForObjectId($obj->objectId, $featureFunctionId, $paramData);
    }
  }

  $message="Konfiguration wurde durchgeführt";
}

setupTreeAndContent("globalConfig_design.html", $message);

$html = str_replace("%ID%",$id, $html);

$erg = QUERY("select name from featureClasses where id='$id' limit 1");
if ($obj=mysqli_fetch_OBJECT($erg))
{
  $html = str_replace("%TITLE%","Feature ".$obj->name." global konfigurieren", $html);
}
else die("FeatureClass $id nicht gefunden");

$instanceTag = getTag("%INSTANCES%",$html);
$instances="";
$erg = QUERY("select id, controllerId, name from featureInstances where featureClassesId='$id' order by name");
while($obj=mysqli_fetch_OBJECT($erg))
{
  $actTag = $instanceTag;
  if ($controllers[$obj->controllerId]->online!=1) continue;
  $actTag = str_replace("%FEATURE_NAME%",$controllers[$obj->controllerId]->name." - ".$obj->name, $actTag);
  $actTag = str_replace("%FEATURE_INSTANCE_ID%",$obj->id, $actTag);
  $instances.=$actTag;
}
$html = str_replace("%INSTANCES%", $instances, $html);

if ($featureFunctionId=="")
{
  chooseTag("%OPT_NO_CONFIG%", $html);
  removeTag("%OPT_CONFIG%", $html);
}
else
{
  removeTag("%OPT_NO_CONFIG%", $html);
  chooseTag("%OPT_CONFIG%", $html);

  $allFeatureFunctionEnums = readFeatureFunctionEnums();
  
  $paramTag = getTag("%PARAM%", $html);
  $params="";
  $erg2 = QUERY("select id,name,type,comment from featureFunctionParams where featureFunctionId='$featureFunctionId' order by id");
  while($obj2=mysqli_fetch_OBJECT($erg2))
  {
    $actTag = $paramTag;
    $actTag = str_replace("%PARAM_NAME%",$obj2->name,$actTag);
    if ($obj2->type=="ENUM")
    {
      $type="<select name='param".$obj2->id."'>";
      foreach($allFeatureFunctionEnums as $obj3)
      {
      	if ($obj3->featureFunctionId==$featureFunctionId && $obj3->paramId==$obj2->id)
      	{
          $type.="<option value='$obj3->value'>$obj3->name";
        }
      }
      $type.="</select>";
    }
    else if ($obj2->type=="BITMASK")
    {
      $type=getBitMask("param".$obj2->id,0, readFeatureFunctionBitmaskNames($featureFunctionId, $obj2->id));
    }
    else if ($obj2->type=="WEEKTIME")
    {
      $type=getWeekTime("param".$obj2->id, 0);
    }
    else $type="<input name='param".$obj2->id."' type='text' size=5>";

    $actTag = str_replace("%PARAM_ENTRY%",$type,$actTag);
    $actTag = str_replace("%COMMENT%",$obj2->comment,$actTag);
    $params.=$actTag;
  }
  $html = str_replace("%PARAM%",$params, $html);
}
show();

?>
