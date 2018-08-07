<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($action == "addMember")
{
  if ($submitted == 1) // deprecated
  {
    QUERY("DELETE from groupFeatures where groupId='$id'");
    
    $erg = QUERY("select id from featureInstances");
    while ( $obj = mysqli_fetch_OBJECT($erg) )
    {
      $act = "id" . $obj->id;
      $act = $$act;
      if ($act == 1)
      {
        QUERY("INSERT into groupFeatures (groupId, featureInstanceId) values('$id','$obj->id')");
        /*$erg2 = QUERY("select id from featureInstances where parentInstanceId='$obj->id'");
        	while($row2=mysqli_fetch_row($erg2))
        	{
        		QUERY("INSERT into groupFeatures (groupId, featureInstanceId) values('$id','$row2[0]')") ;
        	}*/
      }
    }
    
    header("Location: editGroup.php?id=$id");
    exit();
  }
  else
  {
    setupTreeAndContent("addGroupMember_design.html");
    $html = str_replace("%ACTION_TYPE%", $action, $html);
    
    $closeTreeFolder = "</ul></li> \n";
    
    $treeElements = "";
    $treeElements .= addToTree("<a href='editGroup.php?id=$id'>Feature zur Gruppe hinzufügen</a>", 1);
    $html = str_replace("%INITIAL_ELEMENT2%", "expandToItem('tree2','$treeElementCount');", $html);
    
    $allActionClasses = readFeatureClassesThatSupportType("ACTION");
    //$allSignalClasses= readFeatureClassesThatSupportType("EVENT");
    $allMyGroupFeatures = readGroupFeatures($id);
    //$logicalButtonClass = getClassesIdByName("LogicalButton");
    

    unset($ready);
    $lastRoom = "";
    $erg = QUERY("select rooms.id as roomId, rooms.name as roomName,
                                 roomFeatures.featureInstanceId,
                                 featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                                 featureClasses.name as featureClassName,
                                 featureFunctions.id  as featureFunctionId,featureFunctions.name  as featureFunctionName
                                 
                                 from rooms
                                 join roomFeatures on (roomFeatures.roomId = rooms.id)
                                 join featureInstances on (featureInstances.id = roomFeatures.featureInstanceId)
                                 join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                                 join featureFunctions on (featureInstances.featureClassesId=featureFunctions.featureClassesId) 
                                 
                                 where featureFunctions.type='ACTION' 
                                 order by roomName,featureClassName,featureInstanceName"); // and featureInstances.featureClassesId !='$logicalButtonClass'
    while ( $obj = mysqli_fetch_object($erg) )
    {
      $ready[$obj->featureInstanceId] = 1;
      
      if ($allActionClasses[$obj->featureClassesId] == 1)
      {
        if ($obj->roomId != $lastRoom)
        {
          if ($lastRoom != "")
          {
            $treeElements .= $closeTreeFolder; // letzte featureclass
            $treeElements .= $closeTreeFolder; // letzter raum
          }
          $lastRoom = $obj->roomId;
          $treeElements .= addToTree($obj->roomName, 1);
          $lastClass = "";
        }
        
        if ($obj->featureClassesId != $lastClass)
        {
          if ($lastClass != "")
          {
            $treeElements .= $closeTreeFolder; // letzte featureclass
          }
          
          $lastClass = $obj->featureClassesId;
          $treeElements .= addToTree($obj->featureClassName, 1);
          $lastInstance = "";
        }
        
        if ($obj->featureInstanceId != $lastInstance)
        {
          $lastInstance = $obj->featureInstanceId;
          
          if ($allMyGroupFeatures[$obj->featureInstanceId] != "")
          {
            $checked = "checked";
            $assigned .= "expandToItem('tree2'," . $treeElementCount . ");\n";
          }
          else
            $checked = "";
          
          $treeElements .= addToTree("<input type='checkbox' name='id$obj->featureInstanceId' value='1' $checked>$obj->featureInstanceName", 0);
        }
      }
    }
    
    $treeElements .= $closeTreeFolder; // letzte featureclass
    $treeElements .= $closeTreeFolder; // letzter raum
    

    $lastRoom = "";
    $lastController = "";
    $erg = QUERY("select featureInstances.id as featureInstanceId, featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                                 featureClasses.name as featureClassName,
                                 controller.id as controllerId, controller.name as controllerName,
                                 featureFunctions.id  as featureFunctionId,featureFunctions.name  as featureFunctionName
                                 
                                 from featureInstances
                                 join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                                 join controller on (featureInstances.controllerId = controller.id)
                                 join featureFunctions on (featureInstances.featureClassesId=featureFunctions.featureClassesId) 
                                 
                                 where featureFunctions.type='ACTION'
                                 order by controllerName, featureClassName,featureInstanceName,featureFunctionName"); // and featureInstances.featureClassesId !='$logicalButtonClass'
    while ( $obj = mysqli_fetch_object($erg) )
    {
      if ($ready[$obj->featureInstanceId] == 1)
        continue;
      
      if ($allActionClasses[$obj->featureClassesId] == 1)
      {
        if ($lastRoom == "")
        {
          $lastRoom = "dummy";
          $treeElements .= addToTree("Keinem Raum zugeordnet", 1);
        }
        
        if ($obj->controllerId != $lastController)
        {
          if ($lastController != "")
          {
            $treeElements .= $closeTreeFolder; // letzte class
            $treeElements .= $closeTreeFolder; // letzter controller
          }
          $lastController = $obj->controllerId;
          $treeElements .= addToTree($obj->controllerName, 1);
          $lastClass = "";
        }
        
        if ($obj->featureClassesId != $lastClass)
        {
          if ($lastClass != "")
          {
            $treeElements .= $closeTreeFolder; // letzte featureclass
          }
          
          $lastClass = $obj->featureClassesId;
          $treeElements .= addToTree($obj->featureClassName, 1);
          $lastInstance = "";
        }
        
        if ($obj->featureInstanceId != $lastInstance)
        {
          $lastInstance = $obj->featureInstanceId;
          
          if ($allMyGroupFeatures[$obj->featureInstanceId] != "")
          {
            $checked = "checked";
            $assigned .= "expandToItem('tree2'," . $treeElementCount . ");\n";
          }
          else
            $checked = "";
          
          $treeElements .= addToTree("<input type='checkbox' name='id$obj->featureInstanceId' value='1' $checked>$obj->featureInstanceName", 0);
        }
      }
    }
    
    $treeElements .= $closeTreeFolder; // letzte featureclass
    $treeElements .= $closeTreeFolder; // letzter controller
    $treeElements .= $closeTreeFolder; // letzter raum
    

    $html = str_replace("%TREE_ELEMENTS%", $treeElements, $html);
    $html = str_replace("%ID%", $id, $html);
    $html = str_replace("%ASSIGNED%", $assigned, $html);
    
    show();
  }
}
else if ($action == "addSignal")
{
  if ($submitted == 1) // deprecated
  {
    QUERY("DELETE from groupFeatures where groupId='$id'");
    
    $erg = QUERY("select id from featureInstances");
    while ( $obj = mysqli_fetch_OBJECT($erg) )
    {
      $act = "id" . $obj->id;
      $act = $$act;
      if ($act == 1)
      {
        QUERY("INSERT into groupFeatures (groupId, featureInstanceId) values('$id','$obj->id')");
      }
    }
    
    header("Location: editGroup.php?id=$id");
    exit();
  }
  else
  {
    setupTreeAndContent("addGroupMember_design.html");
    $html = str_replace("%ACTION_TYPE%", $action, $html);
    
    $closeTreeFolder = "</ul></li> \n";
    
    $treeElements = "";
    $treeElements .= addToTree("<a href='editGroup.php?id=$id'>Signale zur Gruppe hinzufügen</a>", 1);
    $html = str_replace("%INITIAL_ELEMENT2%", "expandToItem('tree2','$treeElementCount');", $html);
    $allMyGroupFeatures = readGroupFeatures($id);
    
    unset($ready);
    $lastRoom = "";
    $erg = QUERY("select rooms.id as roomId, rooms.name as roomName,
                                 roomFeatures.featureInstanceId,
                                 featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                                 featureClasses.name as featureClassName,
                                 featureFunctions.id  as featureFunctionId,featureFunctions.name  as featureFunctionName
                                 
                                 from rooms
                                 join roomFeatures on (roomFeatures.roomId = rooms.id)
                                 join featureInstances on (featureInstances.id = roomFeatures.featureInstanceId)
                                 join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                                 join featureFunctions on (featureInstances.featureClassesId=featureFunctions.featureClassesId) 
                                 where featureFunctions.type='EVENT' 
                                 order by roomName,featureClassName,featureInstanceName"); // and featureInstances.featureClassesId !='$logicalButtonClass'
    while ( $obj = mysqli_fetch_object($erg) )
    {
      $ready[$obj->featureInstanceId] = 1;
      
      if ($obj->roomId != $lastRoom)
      {
        if ($lastRoom != "")
        {
          $treeElements .= $closeTreeFolder; // letzte featureclass
          $treeElements .= $closeTreeFolder; // letzter raum
        }
        $lastRoom = $obj->roomId;
        $treeElements .= addToTree($obj->roomName, 1);
        $lastClass = "";
      }
      
      if ($obj->featureClassesId != $lastClass)
      {
        if ($lastClass != "")
        {
          $treeElements .= $closeTreeFolder; // letzte featureclass
        }
        
        $lastClass = $obj->featureClassesId;
        $treeElements .= addToTree($obj->featureClassName, 1);
        $lastInstance = "";
      }
      
      if ($obj->featureInstanceId != $lastInstance)
      {
        $lastInstance = $obj->featureInstanceId;
        
        if ($allMyGroupFeatures[$obj->featureInstanceId] != "")
        {
          $checked = "checked";
          $assigned .= "expandToItem('tree2'," . $treeElementCount . ");\n";
        }
        else
          $checked = "";
        
        $treeElements .= addToTree("<input type='checkbox' name='id$obj->featureInstanceId' value='1' $checked>$obj->featureInstanceName", 0);
      }
    }
    
    $treeElements .= $closeTreeFolder; // letzte featureclass
    $treeElements .= $closeTreeFolder; // letzter raum
    

    $lastRoom = "";
    $lastController = "";
    $erg = QUERY("select featureInstances.id as featureInstanceId, featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                                 featureClasses.name as featureClassName,
                                 controller.id as controllerId, controller.name as controllerName,
                                 featureFunctions.id  as featureFunctionId,featureFunctions.name  as featureFunctionName
                                 
                                 from featureInstances
                                 join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                                 join controller on (featureInstances.controllerId = controller.id)
                                 join featureFunctions on (featureInstances.featureClassesId=featureFunctions.featureClassesId) 
                                 
                                 where featureFunctions.type='ACTION'
                                 order by controllerName, featureClassName,featureInstanceName,featureFunctionName"); // and featureInstances.featureClassesId !='$logicalButtonClass'
    while ( $obj = mysqli_fetch_object($erg) )
    {
      if ($ready[$obj->featureInstanceId] == 1)
        continue;
      
      if ($lastRoom == "")
      {
        $lastRoom = "dummy";
        $treeElements .= addToTree("Keinem Raum zugeordnet", 1);
      }
      
      if ($obj->controllerId != $lastController)
      {
        if ($lastController != "")
        {
          $treeElements .= $closeTreeFolder; // letzte class
          $treeElements .= $closeTreeFolder; // letzter controller
        }
        $lastController = $obj->controllerId;
        $treeElements .= addToTree($obj->controllerName, 1);
        $lastClass = "";
      }
      
      if ($obj->featureClassesId != $lastClass)
      {
        if ($lastClass != "")
        {
          $treeElements .= $closeTreeFolder; // letzte featureclass
        }
        
        $lastClass = $obj->featureClassesId;
        $treeElements .= addToTree($obj->featureClassName, 1);
        $lastInstance = "";
      }
      
      if ($obj->featureInstanceId != $lastInstance)
      {
        $lastInstance = $obj->featureInstanceId;
        
        if ($allMyGroupFeatures[$obj->featureInstanceId] != "")
        {
          $checked = "checked";
          $assigned .= "expandToItem('tree2'," . $treeElementCount . ");\n";
        }
        else
          $checked = "";
        
        $treeElements .= addToTree("<input type='checkbox' name='id$obj->featureInstanceId' value='1' $checked>$obj->featureInstanceName", 0);
      }
    }
    
    $treeElements .= $closeTreeFolder; // letzte featureclass
    $treeElements .= $closeTreeFolder; // letzter controller
    $treeElements .= $closeTreeFolder; // letzter raum
    

    $html = str_replace("%TREE_ELEMENTS%", $treeElements, $html);
    $html = str_replace("%ID%", $id, $html);
    $html = str_replace("%ASSIGNED%", $assigned, $html);
    
    show();
  }
}
else if ($action == "deleteMember")
{
  deleteGroupFeature($memberId);
  header("Location: editGroup.php?id=$id");
  exit();
}
else if ($action == "copy")
{
  $erg = QUERY("select name,groupType from groups where id='$id' limit 1");
  if ($obj = mysqli_fetch_OBJECT($erg))
  {
    $name = "Kopie von " . $obj->name;
    
    QUERY("INSERT into groups (name,single,groupType) values('$name','0','$obj->groupType')");
    $newId = query_insert_id();
    
    $erg2 = QUERY("select name,value,basics from groupStates where groupId='$id'");
    while ( $obj2 = mysqli_fetch_OBJECT($erg2) )
    {
      QUERY("INSERT into groupStates (groupId,name, value,basics) values ('$newId','$obj2->name','$obj2->value','$obj2->basics')");
    }
    
    $erg = QUERY("select featureInstanceId from groupFeatures where groupId='$id'");
    while ( $obj = mysqli_fetch_OBJECT($erg) )
    {
      QUERY("INSERT into groupFeatures (featureInstanceId, groupId) values('$obj->featureInstanceId','$newId')");
    }
    
    $erg = QUERY("select name,groupType from groups where subOf='$id' order by id");
    while ($obj = mysqli_fetch_OBJECT($erg))
    {
      $name = "Kopie von " . $obj->name;
      
      QUERY("INSERT into groups (name,single,groupType,subOf) values('$name','0','$obj->groupType','$newId')");
      $innerNewId = query_insert_id();
      
      $erg2 = QUERY("select name,value,basics from groupStates where groupId='$obj->id'");
      while ( $obj2 = mysqli_fetch_OBJECT($erg2) )
      {
        QUERY("INSERT into groupStates (groupId,name, value,basics) values ('$innerNewId','$obj2->name','$obj2->value','$obj2->basics')");
      }
      
      $erg3 = QUERY("select featureInstanceId from groupFeatures where groupId='$obj->id'");
      while ( $obj3 = mysqli_fetch_OBJECT($erg3) )
      {
        QUERY("INSERT into groupFeatures (featureInstanceId, groupId) values('$obj3->featureInstanceId','$innerNewId')");
      }
    }
    
    header("Location: editGroup.php?id=$newId");
    exit();
  }
  else
    die("FEHLER! Ungültige ID $id");
}
else if ($submitted != "")
{
  if ($id == "")
  {
    QUERY("INSERT into groups (name,single,subOf,groupType) values('$name','0','$sub','$groupType')");
    $id = query_insert_id();
    
    $schalterClassesId = getClassesIdByName("Schalter");
    $basicStateNames = getBasicStateNames($schalterClassesId);
    $offName = $basicStateNames->offName;
    $onName = $basicStateNames->onName;
    QUERY("INSERT into groupStates (groupId, name,basics,generated) values('$id','$offName','1','0')");
    QUERY("INSERT into groupStates (groupId, name,basics,generated) values('$id','$onName','2','0')");
    
    header("Location: editGroup.php?id=$id");
    exit();
  }
  else
  {
    if ($delete == 1)
    {
      deleteGroup($id);
      die("<script>top.location='index.php';</script>");
      header("Location: editGroup.php");
      exit();
    }
    else
      QUERY("UPDATE groups set name='$name',groupType='$groupType' where id='$id' limit 1");
    $message = "Einstellungen gespeichert";
  }
}
else if ($action=="changeActive") QUERY("UPDATE groups set active='$active' where id='$id' limit 1");

setupTreeAndContent("editGroup_design.html", $message);

if ($id == "")
{
  $html = str_replace("%ID%", "", $html);
  $html = str_replace("%TITLE%", "Neue Gruppe anlegen", $html);
  $html = str_replace("%SUBMIT_TITLE%", "Gruppe erstellen", $html);
  $html = str_replace("%NAME%", "", $html);
  $html = str_replace("%CLASS_ID%", "", $html);
  $html = str_replace("%SUB%", "", $html);
  removeTag("%ENTRIES%", $html);
  removeTag("%DELETE%", $html);
  $html = str_replace("%GROUP_TYPE_OPTIONS%", getSelect("", ",SIGNALS-AND,SIGNALS-OR", "Normal,Logische UND-Verknüpfung, Logische ODER-Verknüpfung"), $html);
  chooseTag("%OPT_NORMAL%", $html);
  removeTag("%OPT_SIGNALS_AND%", $html);
  removeTag("%OPT_SIGNALS_OR%", $html);
}
else if ($action == "createSub")
{
  $html = str_replace("%ID%", "", $html);
  $html = str_replace("%SUB%", $id, $html);
  $html = str_replace("%TITLE%", "Neue Subgruppe anlegen", $html);
  $html = str_replace("%SUBMIT_TITLE%", "Subgruppe erstellen", $html);
  $html = str_replace("%NAME%", "", $html);
  $html = str_replace("%CLASS_ID%", "", $html);
  removeTag("%ENTRIES%", $html);
  removeTag("%DELETE%", $html);
  
  $html = str_replace("%GROUP_TYPE_OPTIONS%", getSelect("", ",SIGNALS-AND,SIGNALS-OR", "Normal,Logische UND-Verknüpfung, Logische ODER-Verknüpfung"), $html);
  chooseTag("%OPT_NORMAL%", $html);
  removeTag("%OPT_SIGNALS_AND%", $html);
  removeTag("%OPT_SIGNALS_OR%", $html);
}
else
{
  $html = str_replace("%ID%", $id, $html);
  $html = str_replace("%TITLE%", "Gruppe bearbeiten", $html);
  $html = str_replace("%SUBMIT_TITLE%", "Ändern", $html);
  $html = str_replace("%SUB%", "", $html);
  chooseTag("%DELETE%", $html);
  
  $erg = QUERY("select * from groups where id='$id' limit 1");
  if ($obj = mysqli_fetch_OBJECT($erg))
  {
    $html = str_replace("%NAME%", $obj->name, $html);
    $html = str_replace("%GROUP_TYPE_OPTIONS%", getSelect($obj->groupType, ",SIGNALS-AND,SIGNALS-OR", "Normal,Logische UND-Verknüpfung, Logische ODER-Verknüpfung"), $html);
    if ($obj->groupType == "")
    {
      chooseTag("%OPT_NORMAL%", $html);
      removeTag("%OPT_SIGNALS_AND%", $html);
      removeTag("%OPT_SIGNALS_OR%", $html);
      chooseTag("%ENTRIES%", $html);
      chooseTag("%OPT_COPY_SUB%", $html);
    }
    else if ($obj->groupType == "SIGNALS-AND")
    {
      removeTag("%OPT_NORMAL%", $html);
      chooseTag("%OPT_SIGNALS_AND%", $html);
      removeTag("%OPT_SIGNALS_OR%", $html);
      removeTag("%ENTRIES%", $html);
      removeTag("%OPT_COPY_SUB%", $html);
    }
    else if ($obj->groupType == "SIGNALS-OR")
    {
      removeTag("%OPT_NORMAL%", $html);
      removeTag("%OPT_SIGNALS_AND%", $html);
      chooseTag("%OPT_SIGNALS_OR%", $html);
      removeTag("%ENTRIES%", $html);
      removeTag("%OPT_COPY_SUB%", $html);
    }
    
    if ($obj->active==1)
    {
    	$html = str_replace("%ACTIVITY_IMAGE%","img/online2.gif",$html);
  	  $html = str_replace("%ACTIVE_STATUS%","0",$html);
  	  $html = str_replace("%ACTIVE_TITLE%","Gruppe ist aktiv. Klicken, um sie zu deaktivieren.",$html);
    }
    else
    {
    	$html = str_replace("%ACTIVITY_IMAGE%","img/offline2.gif",$html);
  	  $html = str_replace("%ACTIVE_STATUS%","1",$html);
  	  $html = str_replace("%ACTIVE_TITLE%","Gruppe ist inaktiv. Klicken, um sie zu aktivieren.",$html);
    }
  }
  else
    die("FEHLER! Ungültige ID $id");
  
  $allFeatureInstances = readFeatureInstances();
  $allFeatureClasses = readFeatureClasses();
  
  $membersTag = getTag("%MEMBERS%", $html);
  $erg = QUERY("select id,featureInstanceId from groupFeatures where groupId='$id' order by id");
  while ( $obj = mysqli_fetch_OBJECT($erg) )
  {
    $actTag = $membersTag;
    $actTag = str_replace("%MEMBER_ID%", $obj->id, $actTag);
    
    $roomName = getRoomForFeatureInstance($obj->featureInstanceId)->name;
    $actTag = str_replace("%MEMBER_ROOM%", $roomName, $actTag);
    
    $actFeatureInstance = $allFeatureInstances[$obj->featureInstanceId];
    $actTag = str_replace("%MEMBER_NAME%", $actFeatureInstance->name, $actTag);
    
    $actClass = $allFeatureClasses[$actFeatureInstance->featureClassesId];
    $actTag = str_replace("%MEMBER_CLASS%", $actClass->name, $actTag);
    
    $membersByRoom[$roomName].=$actTag;
  }
  
  ksort($membersByRoom);
  
  $members = "";
  foreach($membersByRoom as $val)
  {
    $members.=$val;
  }
  $html = str_replace("%MEMBERS%", $members, $html);
}

show();

?>
