<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted!="")
{
  if ($delete==1)
  {
    deleteFeatureFunction($id);
    header("Location: editFeatureClass.php?id=$featureClassesId");
    exit;
  }
  else
  {
    if (($type=="RESULT" || $type=="EVENT") && $functionId<100) $message="FEHLER! Funktionsids für Events und Results müssen >= 100 sein";
    else
    {
      $name=trim($name);
      if($id=="")
      {
      	$wasNew=1;
        MYSQL_QUERY("INSERT into featureFunctions (featureClassesId,type,name,functionId,view) values('$featureClassesId','$type', '$name','$functionId','$view')") or die(MYSQL_ERROR());
        $id = mysql_insert_id();
      }
      else
      {
        MYSQL_QUERY("UPDATE featureFunctions set type='$type',name='$name',functionId='$functionId',view='$view' where id='$id' limit 1") or die(MYSQL_ERROR());
      }
       
      $parts = explode(",",$paramIds);
      foreach ((array)$parts as $actId)
      {
        $name="paramName$actId";
        $name=trim($$name);
        $type="paramType$actId";
        $type=$$type;
        $comment = "paramComment$actId";
        $comment = $$comment;
        $view = "view$actId";
        $view=$$view;
         
        $erg = MYSQL_QUERY("select id from featureFunctionParams where id='$actId' limit 1") or die(MYSQL_ERROR());
        if ($row=MYSQL_FETCH_ROW($erg))
        {
        	if ($name=="")
        	{
        		if ($wasNew!=1) deleteFeatureFunctionParam($actId);
        	}
        	else MYSQL_QUERY("UPDATE featureFunctionParams set name='$name',type='$type', comment='$comment',view='$view' where id='$actId' limit 1") or die(MYSQL_ERROR());
        }
        else if ($name!="")
        	MYSQL_QUERY("INSERT into featureFunctionParams (featureFunctionId, name, type, comment,view) values('$id','$name','$type', '$comment','$view')") or die(MYSQL_ERROR());

        if ($name!="")
        {
          if ($type=="ENUM")
          {
            $enumParts = "paramEnum".$actId."_ids";
            $enumParts = $$enumParts;
            $enumParts = explode(",",$enumParts);
            foreach ((array)$enumParts as $enumId)
            {
              $paramEnumName="paramEnumName".$actId."_".$enumId;
              $paramEnumName = trim($$paramEnumName);
              $paramEnumValue="paramEnumValue".$actId."_".$enumId;
              $paramEnumValue = $$paramEnumValue;
               
                $erg = MYSQL_QUERY("select id from featureFunctionEnums where id='$enumId' limit 1") or die(MYSQL_ERROR());
                if ($row=MYSQL_FETCH_ROW($erg))
                {
                	if ($paramEnumName=="")
                	{
                		if ($wasNew!=1) deleteFeatureFunctionEnum($enumId);
                	}
                	else
                	{
                		MYSQL_QUERY("UPDATE featureFunctionEnums set name='$paramEnumName',value='$paramEnumValue' where id='$enumId' limit 1") or die(MYSQL_ERROR());
                	}
                }
                else if ($paramEnumName!="")
                	MYSQL_QUERY("INSERT into featureFunctionEnums (featureFunctionId, paramId, name,value) values('$id','$actId','$paramEnumName', '$paramEnumValue')") or die(MYSQL_ERROR());
            }
          }
          else if ($type=="BITMASK")
          {
          	for($bitPos=0;$bitPos<8;$bitPos++)
          	{
              $paramBitName="paramBitName".$actId."_".$bitPos;
              $paramBitName = trim($$paramBitName);
               
              $erg = MYSQL_QUERY("select id from featureFunctionBitmasks where featureFunctionId='$id' and paramId='$actId' and bit='$bitPos' limit 1") or die(MYSQL_ERROR());
              if ($row=MYSQL_FETCH_ROW($erg)) MYSQL_QUERY("UPDATE featureFunctionBitmasks set name='$paramBitName' where id='$row[0]' limit 1") or die(MYSQL_ERROR());
              else MYSQL_QUERY("INSERT into featureFunctionBitmasks (featureFunctionId, paramId, name,bit) values('$id','$actId','$paramBitName', '$bitPos')") or die(MYSQL_ERROR());
            }
          }
        }
      }
      header("Location: editFeatureFunction.php?id=$id&featureClassesId=$featureClassesId&message=".urlencode("Die Änderungen wurden gespeichert"));
      exit;
    }
  }
}

setupTreeAndContent("editFeatureFunction_design.html", $message);

$html = str_replace("%FEATURE_CLASSES_ID%",$featureClassesId, $html);

if ($id=="")
{
  $html = str_replace("%ID%","", $html);
  $html = str_replace("%TITLE%","Neue Featurefunktion anlegen", $html);
  $html = str_replace("%SUBMIT_TITLE%","Featurefunktion erstellen", $html);
  $html = str_replace("%NAME%","", $html);
  $html = str_replace("%FUNCTION_ID%","", $html);
  $typeOptions = getSelect("EVENT","EVENT,ACTION,FUNCTION,RESULT");
  $html = str_replace("%TYPE_OPTIONS%",$typeOptions, $html);
  removeTag("%DELETE%",$html);

  $paramsTag = getTag("%PARAMS%", $html);
  $params="";
  for ($i=0;$i<5;$i++)
  {
    $actTag = $paramsTag;
    removeTag("%BITMASK%",$actTag);
    $actTag = str_replace("%PARAM_TITLE%","Parameter".($i+1),$actTag);
    $actTag = str_replace("%PARAM_ID%",$i,$actTag);
    $actTag = str_replace("%PARAM_NAME%","",$actTag);
    $actTag = str_replace("%COMMENT%","",$actTag);
    
    $paramTypeOptions = getSelect($obj2->type,"ENUM,BYTE,WORD,DWORD,STRING,WORDLIST,BLOB,WEEKTIME,BITMASK");
    $actTag = str_replace("%PARAM_TYPE_OPTIONS%",$paramTypeOptions, $actTag);
     
    removeTag("%ENUM%",$actTag);
    $params.=$actTag;
  }
  $html = str_replace("%PARAMS%",$params, $html);
  $html = str_replace("%PARAM_IDS%","0,1,2,3,4", $html);
  
  $ansicht="Standard";
}
else
{
  $html = str_replace("%ID%",$id, $html);
  $html = str_replace("%TITLE%","Featurefunktion bearbeiten", $html);
  $html = str_replace("%SUBMIT_TITLE%","Featurefunktion ändern", $html);
  chooseTag("%DELETE%",$html);

  $allFeatureFunctions = readFeatureFunctions();
  $allFeatureFunctionParams = readFeatureFunctionParams();
  $allFeatureFunctionEnums = readFeatureFunctionEnums();
  $allFeatureFunctionBitmasks = readFeatureFunctionBitmasks();
  
  foreach($allFeatureFunctions as $obj)
  {
  	if ($obj->id==$id)
  	{
      $typeOptions = getSelect($obj->type,"EVENT,ACTION,FUNCTION,RESULT");
      $html = str_replace("%TYPE_OPTIONS%",$typeOptions, $html);
      $html = str_replace("%NAME%",$obj->name, $html);
      $html = str_replace("%FUNCTION_ID%",$obj->functionId, $html);
      
      $ansicht=$obj->view;

      $paramsTag = getTag("%PARAMS%", $html);
      $paramIds="";
      $paramCount=0;
      $params="";
      foreach($allFeatureFunctionParams as $obj2)
      {
      	if ($obj2->featureFunctionId==$obj->id)
      	{
          $actTag = $paramsTag;
          $actTag = str_replace("%PARAM_TITLE%","Parameter".($paramCount+1),$actTag);
          $actTag = str_replace("%PARAM_ID%",$obj2->id, $actTag);
          $actTag = str_replace("%PARAM_NAME%",$obj2->name,$actTag);
          $paramTypeOptions = getSelect($obj2->type,"ENUM,BYTE,WORD,DWORD,STRING,WORDLIST,BLOB,WEEKTIME,BITMASK");

          if ($obj2->view=="Standard")
          {
            $actTag = str_replace("%standardChecked%","checked",$actTag);
            $actTag = str_replace("%experteChecked%","",$actTag);
            $actTag = str_replace("%entwicklerChecked%","",$actTag);
          }
          else  if ($obj2->view=="Experte")
          {
            $actTag = str_replace("%standardChecked%","",$actTag);
            $actTag = str_replace("%experteChecked%","checked",$actTag);
            $actTag = str_replace("%entwicklerChecked%","",$actTag);
          }
          else  if ($obj2->view=="Entwickler")
          {
            $actTag = str_replace("%standardChecked%","",$actTag);
            $actTag = str_replace("%experteChecked%","",$actTag);
            $actTag = str_replace("%entwicklerChecked%","checked",$actTag);
          }
          
          if ($obj2->type=="ENUM")
          {
            chooseTag("%ENUM%",$actTag);
            $actTag = str_replace("%PARAM_ID%",$obj2->id,$actTag);
            $enumValueTag = getTag("%ENUM_VALUE%", $actTag);
            $enumValueIds="";
            $enumValueCount=0;
            $highestEnumValueId=0;
            $enumValues="";
            foreach($allFeatureFunctionEnums as $obj3)
            {
            	if ($obj3->featureFunctionId==$obj->id && $obj3->paramId==$obj2->id)
            	{
                $actEnumTag = $enumValueTag;
                $actEnumTag = str_replace("%ENUM_ID%",$obj3->id,$actEnumTag);
                $actEnumTag = str_replace("%ENUM_NAME%",$obj3->name,$actEnumTag);
                $actEnumTag = str_replace("%ENUM_VALUE%",$obj3->value,$actEnumTag);
           
                $enumValues.=$actEnumTag;
                if ($enumValueIds!="") $enumValueIds.=",";
                $enumValueIds.=$obj3->id;
                $enumValueCount++;
              }
              
              if ($obj3->id>$highestEnumValueId)
                $highestEnumValueId=$obj3->id;
            }
         
            $highestEnumValueId++;
            if ($enumValueIds!="") $enumValueIds.=",";
            $enumValueIds.=$highestEnumValueId;

            $actEnumTag = $enumValueTag;
            $actEnumTag = str_replace("%ENUM_ID%",$highestEnumValueId,$actEnumTag);
            $actEnumTag = str_replace("%ENUM_NAME%","",$actEnumTag);
            $actEnumTag = str_replace("%ENUM_VALUE%","",$actEnumTag);
            $enumValues.=$actEnumTag;
         
            $actTag = str_replace("%ENUM_VALUE%",$enumValues, $actTag);
            $actTag = str_replace("%PARAM_ENUM_IDS%",$enumValueIds, $actTag);
         }
         else
           removeTag("%ENUM%",$actTag);
           
          if ($obj2->type=="BITMASK")
          {
            chooseTag("%BITMASK%",$actTag);
            $actTag = str_replace("%PARAM_ID%",$obj2->id,$actTag);
            $bitValueTag = getTag("%BIT_VALUE%", $actTag);
            $bitValues="";
            foreach((array)$allFeatureFunctionBitmasks as $obj3)
            {
            	if ($obj3->featureFunctionId==$obj->id && $obj3->paramId==$obj2->id)
            	{
                $actBitTag = $bitValueTag;
                $actBitTag = str_replace("%BIT_POS%",$obj3->bit,$actBitTag);
                $actBitTag = str_replace("%BIT_NAME%",$obj3->name,$actBitTag);
                $bitValues.=$actBitTag;
              }
            }
            $actTag = str_replace("%BIT_VALUE%",$bitValues, $actTag);
         }
         else
           removeTag("%BITMASK%",$actTag);

         $actTag = str_replace("%PARAM_TYPE_OPTIONS%",$paramTypeOptions, $actTag);
         $actTag = str_replace("%COMMENT%",$obj2->comment,$actTag);
         $params.=$actTag;

         if ($paramIds!="") $paramIds.=",";
         $paramIds.=$obj2->id;

         $paramCount++;
       }
     }

     $erg = MYSQL_QUERY("select max(id) from featureFunctionParams") or die(MYSQL_ERROR());
     if ($row=MYSQL_FETCH_ROW($erg)) $nextId=$row[0]+1;
     else $nextId=1;

     if ($paramIds!="") $paramIds.=",";
     $paramIds.=$nextId;

     $actTag = $paramsTag;
     $actTag = str_replace("%PARAM_TITLE%","Parameter".($paramCount+1),$actTag);
     $actTag = str_replace("%PARAM_ID%",$nextId,$actTag);
     $actTag = str_replace("%PARAM_NAME%","",$actTag);
     $paramTypeOptions = getSelect($obj2->type,"ENUM,BYTE,WORD,DWORD,STRING,WORDLIST,BLOB,WEEKTIME,BITMASK");
     $actTag = str_replace("%PARAM_TYPE_OPTIONS%",$paramTypeOptions, $actTag);
     $actTag = str_replace("%COMMENT%","",$actTag);
     $actTag = str_replace("%standardChecked%","checked",$actTag);
     $actTag = str_replace("%experteChecked%","",$actTag);
     $actTag = str_replace("%entwicklerChecked%","",$actTag);
     removeTag("%ENUM%",$actTag);
     removeTag("%BITMASK%",$actTag);
     $params.=$actTag;

      $html = str_replace("%PARAMS%",$params, $html);
      $html = str_replace("%PARAM_IDS%",$paramIds, $html);
      break;
   }
 }
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
