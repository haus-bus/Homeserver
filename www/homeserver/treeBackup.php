<?php

function show($exit = 1)
{
  global $html;
  global $scriptStart;
  global $debugTime;
  
  removeTag("%MESSAGE%", $html);
  //debugScript("ende");
  echo $html;
  
  if ($debugTime == 1)
  {
    $scriptDuration = (microtime(TRUE) - $scriptStart) * 1000;
    echo "<hr>$scriptDuration ms";
  }
  if ($exit == 1)
    exit();
}

function showMessageNonExit($message, $title = "", $link = "", $linkName = "", $link2 = "", $link2Name = "")
{
  showMessage($message, $title, $link, $linkName, $link2, $link2Name, 0);
}

function showMessage($message, $title = "", $link = "", $linkName = "", $link2 = "", $link2Name = "", $exit = 1)
{
  global $html;
  
  setupTreeAndContent("message_design.html");
  $html = str_replace("%TITLE%", $title, $html);
  $html = str_replace("%MESSAGE%", $message, $html);
  
  if ($link == "")
    $html = removeTag("%OPT_LINK%", $html);
  else
  {
    $html = chooseTag("%OPT_LINK%", $html);
    $html = str_replace("%LINK%", $link, $html);
    $html = str_replace("%LINK_NAME%", $linkName, $html);
  }
  if ($link2 == "")
    $html = removeTag("%OPT_LINK2%", $html);
  else
  {
    $html = chooseTag("%OPT_LINK2%", $html);
    $html = str_replace("%LINK2%", $link2, $html);
    $html = str_replace("%LINK2_NAME%", $link2Name, $html);
  }
  
  echo $html;
  if ($exit == 1)
    exit();
}

function setupTreeAndContent($content = "")
{
  global $html;
  global $message;
  global $CONTROLLER_CLASSES_ID;
  global $lastOpen;
  global $isFolder;
  global $treeElementCount;
  global $showGeneratedGroups;
  global $tree;
  
  // Welcher Knoten soll ge�ffnet sein
  if ($lastOpen == "")
    $lastOpen = $_SESSION["lastOpened"];
  if ($isFolder == "")
    $isFolder = $_SESSION["lastFolder"];
  $_SESSION["lastOpened"] = $lastOpen;
  $_SESSION["lastFolder"] = $isFolder;
  
  if ($tree == 1)
  {
    $closeTreeFolder = "</ul></li> \n";

    debugScript("vor loadTemplate");
    
    $html = loadTemplate("frame_design.html");
    
    debugScript("nach loadTemplate");
    
    $viewMode = readViewMode();
    $html = str_replace("%ANSICHT%", $viewMode, $html);

    debugScript("nach viewmode");
    
    $free = readFreeDiskSpace();
    $html = str_replace("%PLATZ%", "Server: ".$free->prozent." voll - ".$free->rest."B frei", $html);

    debugScript("nach free");

    debugScript("nach query0");
    $tasterClassesId = getClassesIdByName("Taster");

    debugScript("nach query1");
    
    $erg = QUERY("select SQL_CACHE groups.id,featureInstanceId from groups join groupFeatures on (groupFeatures.groupId=groups.id) where groups.single='1'");
    while ( $row = MYSQL_FETCH_ROW($erg) )
    {
      $mySingleGroup[$row[1]] = $row[0];
    }
    
    unset($featureClassesOrderedByName);
    $erg = QUERY("select featureClasses.*,group_concat(distinct featureFunctions.type) as functionTypes from featureClasses left join featureFunctions on (featureFunctions.featureClassesId=featureClasses.id) group by featureClasses.id order by featureClasses.name");
    while ($obj = MYSQL_FETCH_OBJECT($erg))
    {
    	 $featureClassesOrderedByName[$obj->id]=$obj;
    }
    
    debugScript("nach query2");
    unset($children);
    $lastClass = "";
    $lastParent = "";
    $erg = QUERY("select parentInstanceId, featureInstances.id as featureInstanceId, featureClassesId as featureClassesId, featureInstances.name as featureInstanceName                               
                  from featureInstances
                  join featureClasses ON ( featureClasses.id = featureInstances.featureClassesId)
                  where parentInstanceId>0 and featureClassesId!='$CONTROLLER_CLASSES_ID'
                  order by parentInstanceId,featureClasses.name, featureInstanceName");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
      if ($lastParent != $obj->parentInstanceId)
      {
        $lastParent = $obj->parentInstanceId;
        $lastClass = "";
      }
      
      $featureClassObj = $featureClassesOrderedByName[$obj->featureClassesId];
      $featureClassView = $featureClassObj->view;
      $featureClassName = $featureClassObj->name;
      $featureClassGuiControl = $featureClassObj->guiControl;
      $featureClassSmoketest = $featureClassObj->smoketest;
      $hasAction = strpos($featureClassObj->functionTypes,$type)!==FALSE;

      if ($ansicht=="Experte" && $featureClassView=="Entwickler") continue;
      if ($ansicht=="Standard" && ($featureClassView=="Experte" || $featureClassView=="Entwickler")) continue;
      
      if ($lastClass != $obj->featureClassesId)
      {
        if ($lastClass != "") $children[$obj->parentInstanceId] .= $closeTreeFolder;
        $lastClass = $obj->featureClassesId;
        $children[$obj->parentInstanceId] .= addToTree($featureClassName, 1);
      }
      
      $children[$obj->parentInstanceId] .= addToTree($obj->featureInstanceName, 1, "editFeatureInstance.php?id=$obj->featureInstanceId", "", "-1", $obj->featureInstanceId);
      
      if ($hasAction)
      {
        $myGroup = $mySingleGroup[$obj->featureInstanceId];
        
        $children[$obj->parentInstanceId] .= addToTree("Basisregeln", 0, "editBaseConfig.php?groupId=$myGroup");
        $children[$obj->parentInstanceId] .= addToTree("Zusatzregeln", 1, "editRules.php?groupId=$myGroup");
        $_SESSION["groupLinkNr" . $myGroup] = $treeElementCount;
        $children[$obj->parentInstanceId] .= addToTree("Zust�nde", 0, "editGroupStates.php?groupId=$myGroup");
        $children[$obj->parentInstanceId] .= $closeTreeFolder;
      }
      
      if (!$hasAction || $obj->featureClassesId == $tasterClassesId) $children[$obj->parentInstanceId] .= addToTree("Regelbeteiligung", 0, "showSensorRules.php?id=$obj->featureInstanceId");
      
      if ($obj->featureClassesId == 14) $children[$obj->parentInstanceId] .= addToTree("Zeiteinmessung", 0, "rolloAdjustment.php?featureInstanceId=" . $obj->featureInstanceId);
      if ($featureClassGuiControl != "") $children[$obj->parentInstanceId] .= addToTree("GUI-Control", 0, "guiControl.php?script=" . $featureClassGuiControl . "&featureInstanceId=" . $obj->featureInstanceId);
      if ($featureClassSmoketest != "") $children[$obj->parentInstanceId] .= addToTree("Smoketest", 0, "guiControl.php?script=tests/" . $featureClassSmoketest . "&featureInstanceId=" . $obj->featureInstanceId);
      
      $children[$obj->parentInstanceId] .= $closeTreeFolder;
    }
    
    debugScript("nach query3");
    
    $treeElements .= addToTree("<b>Homeserver</b>", 1);
    
    if ($lastOpen != "")
    {
      $toOpen = $lastOpen;
      if ($isFolder == 1) $toOpen++;
      $html = str_replace("%INITIAL_ELEMENT%", "expandToItem('tree1','$toOpen');", $html);
      $html = str_replace("%LAST_OPEN%", $lastOpen, $html);
    }
    else
    {
      $html = str_replace("%INITIAL_ELEMENT%", "expandToItem('tree1','$treeElementCount');", $html);
      $html = str_replace("%LAST_OPEN%", $treeElementCount, $html);
    }
    
    $treeElements .= addToTree("System", 1);
    $treeElements .= addToTree("Grundeinstellungen", 1);
    
    if ($viewMode == "Entwickler")
    {
      $treeElements .= addToTree("Featureklassen", 1, "editFeatureClass.php");
      foreach ($featureClassesOrderedByName as $id=>$obj)
      {
        $treeElements .= addToTree($obj->name, 0, "editFeatureClass.php?id=$obj->id");
      }
      $treeElements .= $closeTreeFolder;
    }
    
    $treeElements .= addToTree("Globale Controllerkonfiguration", 1);
    foreach ($featureClassesOrderedByName as $id=>$obj)
    {
      if ($obj->name=="DaliLine" || $obj->name=="TwiLine"  || $obj->name=="Unbekanntes Feature" || $obj->name=="Wetter" || $obj->id==$CONTROLLER_CLASSES_ID) continue;
      $treeElements .= addToTree(i18n($obj->name), 0, "globalConfig.php?id=$obj->id");
    }
    $treeElements .= $closeTreeFolder;
    
    $treeElements .= addToTree("Funktionstemplates", 1);
    foreach ($featureClassesOrderedByName as $id=>$obj)
    {
      if ($obj->name=="DaliLine" || $obj->name=="TwiLine"  || $obj->name=="Unbekanntes Feature" || $obj->name=="Wetter" || $obj->id==$CONTROLLER_CLASSES_ID || $obj->id == 24) continue;

      if (strpos($featureClassesOrderedByName[$obj->id]->functionTypes,"ACTION")!==FALSE) $treeElements .= addToTree(i18n($obj->name), 0, "templateConfig.php?id=$obj->id");
    }
    
    $treeElements .= addToTree("Generisch", 0, "templateConfig.php?id=-1");
    $treeElements .= $closeTreeFolder;
    $treeElements .= addToTree("Weitere Einstellungen", 1);
    $treeElements .= addToTree("LED-Statushelligkeit", 0, "editLedBrightness.php");
    $treeElements .= addToTree("Standorteinstellungen", 0, "editLocation.php");
    $treeElements .= addToTree("Netzwerkeinstellungen", 0, "editNetwork.php");
    $treeElements .= addToTree("Internationalisierung", 0, "editLanguages.php");
    $treeElements .= addToTree("Online Backup", 0, "editOnlineBackup.php");
    $treeElements .= addToTree("Diverse Zusatzparameter", 0, "editAdditionalSettings.php");
    $treeElements .= $closeTreeFolder;
    $treeElements .= $closeTreeFolder;
    
    $treeElements .= addToTree("Controller (%NR_CONTROLLERS%)", 1, "controller.php");
    
    $lastController = "";
    $lastClass = "";
    $nrControllers = 0;
    $erg = QUERY("select SQL_CACHE controller.id as controllerId,controller.name as controllerName,online,
                        featureInstances.id as featureInstanceId, featureClassesId as featureClassesId, featureInstances.name as featureInstanceName, checked
                        from controller 
                        LEFT join featureInstances on (controller.id = featureInstances.controllerId)
                        LEFT join featureClasses ON ( featureClasses.id = featureInstances.featureClassesId)
                        where parentInstanceId=0 and featureClassesId!='$CONTROLLER_CLASSES_ID'
                        order by controllerName, featureClasses.name, featureInstanceName");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
      if ($lastController != $obj->controllerId)
      {
        if ($lastController != "")
        {
          $treeElements .= $closeTreeFolder; // letzte featureclass
          $treeElements .= $closeTreeFolder; // letzter controller 
        }
        $lastController = $obj->controllerId;
        $treeElements .= addToTree($obj->controllerName, 1, "editController.php?id=$obj->controllerId", "", $obj->online);
        $nrControllers++;
        $lastClass = "";
      }
      
      if ($obj->featureClassesId == $CONTROLLER_CLASSES_ID) die("ACHTUNG: Controllerfeature gefunden bei id " . $obj->featureInstanceId);
      
      $featureClassObj = $featureClassesOrderedByName[$obj->featureClassesId];
      $featureClassView = $featureClassObj->view;
      $featureClassName = $featureClassObj->name;
      $featureClassGuiControl = $featureClassObj->guiControl;
      $featureClassSmoketest = $featureClassObj->smoketest;
      $hasAction = strpos($featureClassObj->functionTypes,$type)!==FALSE;
      
      if ($ansicht=="Experte" && $featureClassView=="Entwickler") continue;
      if ($ansicht=="Standard" && ($featureClassView=="Experte" || $featureClassView=="Entwickler")) continue;
      
      if ($lastClass != $obj->featureClassesId)
      {
        if ($lastClass != "") $treeElements .= $closeTreeFolder;
        $lastClass = $obj->featureClassesId;
        $treeElements .= addToTree($featureClassName, 1);
      }
      
      $treeElements .= addToTree($obj->featureInstanceName, 1, "editFeatureInstance.php?id=$obj->featureInstanceId", "", "-1", $obj->featureInstanceId, $obj->checked);
      
      if ($hasAction)
      {
        $myGroup = $mySingleGroup[$obj->featureInstanceId];
        
        $treeElements .= addToTree("Basisregeln", 0, "editBaseConfig.php?groupId=$myGroup");
        $treeElements .= addToTree("Zusatzregeln", 1, "editRules.php?groupId=$myGroup");
        $_SESSION["groupLinkNr" . $myGroup] = $treeElementCount;
        $treeElements .= addToTree("Zust�nde", 0, "editGroupStates.php?groupId=$myGroup");
        $treeElements .= $closeTreeFolder;
      }
      
      if (!$hasAction || $obj->featureClassesId == $tasterClassesId) $treeElements .= addToTree("Regelbeteiligung", 0, "showSensorRules.php?id=$obj->featureInstanceId");
      
      if ($obj->featureClassesId == 14) $treeElements .= addToTree("Zeiteinmessung", 0, "rolloAdjustment.php?featureInstanceId=" . $obj->featureInstanceId);
      if ($featureClassGuiControl != "") $treeElements .= addToTree("GUI-Control", 0, "guiControl.php?script=" . $featureClassGuiControl . "&featureInstanceId=" . $obj->featureInstanceId);
      if ($featureClassSmoketest != "") $treeElements .= addToTree("Smoketest", 0, "guiControl.php?script=tests/" . $featureClassSmoketest . "&featureInstanceId=" . $obj->featureInstanceId);
      
      if ($children[$obj->featureInstanceId] != "")
      {
        $treeElements .= $children[$obj->featureInstanceId];
        $treeElements .= $closeTreeFolder;
      }
      
      $treeElements .= $closeTreeFolder;
    }
    
    if ($nrControllers > 0)
    {
      if ($lastClass != "") $treeElements .= $closeTreeFolder; // letzte featureclass
      $treeElements .= $closeTreeFolder; // letzter controller
    }

    // Bootloader ?
    $lastController = "";
    $erg = QUERY("select SQL_CACHE controller.id as controllerId,controller.name as controllerName,online from controller where bootloader='1' and online='1' order by controllerName");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
      $lastController = $obj->controllerId;
      $treeElements .= addToTree($obj->controllerName, 0, "editController.php?id=$obj->controllerId", "", $obj->online);
      $nrControllers++;
    }
    
    $treeElements .= $closeTreeFolder; // controller ende

    $treeElements = str_replace("%NR_CONTROLLERS%", $nrControllers, $treeElements);
    
    $treeElements .= addToTree("Webapplikation", 1);
    $treeElements .= addToTree("Webseiten", 1, "editButtonPage.php");
    $erg = QUERY("select pos,name,id from webappPages order by pos,name");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
      if ($obj->name=="") $obj->name="[leer]";
      $treeElements .= addToTree($obj->name, 0, "editButtonPage.php?pageId=" . $obj->id);
    }
    $treeElements .= $closeTreeFolder;
    
    $treeElements .= addToTree("Diagramme", 1, "editGraphs.php");
    $erg = QUERY("select id,title from graphs order by title");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
      $treeElements .= addToTree($obj->title, 0, "editGraphs.php?id=" . $obj->id);
    }
    $treeElements .= $closeTreeFolder;
    
    $treeElements .= $closeTreeFolder;
    $treeElements .= addToTree("Regel�bersicht", 0, "showAllRules.php");
    $treeElements .= addToTree("Journal", 0, "journal.php");
    $treeElements .= addToTree("Live Events", 0, "liveEvents.php", "_blank");
    $treeElements .= addToTree("Softwareupdates", 0, "updates.php");
    
    if ($ansicht=="Experte" || $ansicht=="Entwickler") $treeElements .= addToTree("Erstinbetriebnahme", 0, "firstInstall.php");
    $treeElements .= addToTree("Alte Objekte l�schen", 0, "deleteObjects.php");
    $treeElements .= addToTree("Wiederherstellung", 0, "recovery.php");
    $treeElements .= $closeTreeFolder;
    
    $treeElements .= addToTree("R�ume", 1, "editRoom.php");
    $lastRoom = "";
    $erg = QUERY("select SQL_CACHE rooms.id as roomId, rooms.name as roomName,
                               featureInstanceId,
                               featureInstances.featureClassesId as featureClassesId, featureInstances.name as featureInstanceName
                               from rooms 
                               LEFT join roomFeatures on (rooms.id = roomFeatures.roomId)
                               LEFT join featureInstances on (featureInstances.id=featureInstanceId) 
                               LEFT join featureClasses ON ( featureClasses.id = featureInstances.featureClassesId)
                               where (parentInstanceId=0 or parentInstanceId is null) and (featureClassesId!='$CONTROLLER_CLASSES_ID' or featureClassesId is null)
                               order by roomName,featureClasses.name, featureInstanceName");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
      if ($obj->roomId != $lastRoom)
      {
        if ($lastRoom != "")
        {
          if ($classAdded == 1) $treeElements .= $closeTreeFolder; // letzte Class
          $treeElements .= $closeTreeFolder; // letzter raum
        }
        $lastRoom = $obj->roomId;
        $treeElements .= addToTree($obj->roomName, 1, "editRoom.php?id=$obj->roomId");
        $lastClass = "";
        $classAdded = "";
      }

      if ($obj->featureClassesId == null) continue;
      else $classAdded = 1;

      $featureClassObj = $featureClassesOrderedByName[$obj->featureClassesId];
      $featureClassView = $featureClassObj->view;
      $featureClassName = $featureClassObj->name;
      $featureClassGuiControl = $featureClassObj->guiControl;
      $featureClassSmoketest = $featureClassObj->smoketest;
      $hasAction = strpos($featureClassObj->functionTypes,$type)!==FALSE;
      
      if ($ansicht=="Experte" && $featureClassView=="Entwickler") continue;
      if ($ansicht=="Standard" && ($featureClassView=="Experte" || $featureClassView=="Entwickler")) continue;
      
      if ($lastClass != $obj->featureClassesId)
      {
        if ($lastClass != "") $treeElements .= $closeTreeFolder;
        $lastClass = $obj->featureClassesId;
        $treeElements .= addToTree($featureClassName, 1);
      }
      
      $treeElements .= addToTree($obj->featureInstanceName, 1, "editFeatureInstance.php?id=$obj->featureInstanceId", "", "-1", $obj->featureInstanceId);
      
      if ($hasAction)
      {
        $myGroup = $mySingleGroup[$obj->featureInstanceId];
        
        $treeElements .= addToTree("Basisregeln", 0, "editBaseConfig.php?groupId=$myGroup");
        $treeElements .= addToTree("Zusatzregeln", 1, "editRules.php?groupId=$myGroup");
        $_SESSION["groupLinkNr" . $myGroup] = $treeElementCount;
        $treeElements .= addToTree("Zust�nde", 0, "editGroupStates.php?groupId=$myGroup");
        $treeElements .= $closeTreeFolder;
      }
      
      if (!$hasAction || $obj->featureClassesId == $tasterClassesId) $treeElements .= addToTree("Regelbeteiligung", 0, "showSensorRules.php?id=$obj->featureInstanceId");
      
      if ($obj->featureClassesId == 14) $treeElements .= addToTree("Zeiteinmessung", 0, "rolloAdjustment.php?featureInstanceId=" . $obj->featureInstanceId);
      if ($featureClassGuiControl != "") $treeElements .= addToTree("GUI-Control", 0, "guiControl.php?script=" . $featureClassGuiControl . "&featureInstanceId=" . $obj->featureInstanceId);

      if ($children[$obj->featureInstanceId] != "")
      {
        $treeElements .= $children[$obj->featureInstanceId];
        $treeElements .= $closeTreeFolder;
      }
      
      $treeElements .= $closeTreeFolder;
    }
    if ($classAdded == 1) $treeElements .= $closeTreeFolder; // letzte class
    if ($lastRoom != "") $treeElements .= $closeTreeFolder; // letzter raum
    $treeElements .= $closeTreeFolder; // r�ume ende

    $treeElements .= addToTree("Gruppen", 1, "editGroup.php");
    
    $erg = QUERY("select id,name,subOf from groups where subOf>0 order by name");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
      $subs[$obj->id] = $obj;
      $hasSub[$obj->subOf] = 1;
    }
    
    if ($showGeneratedGroups == 0 && $viewMode != "Entwickler") $and = "and generated!='1'";
    
    $erg = QUERY("select id,name,groupType from groups where single!='1' and subOf='0' $and order by name");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
      $treeElements .= addToTree($obj->name, 1, "editGroup.php?id=" . $obj->id);
      
      if ($obj->groupType == "")
      {
        $treeElements .= addToTree("Basisregeln", 0, "editBaseConfig.php?groupId=" . $obj->id);
        $treeElements .= addToTree("Zusatzregeln", 1, "editRules.php?groupId=" . $obj->id);
        $_SESSION["groupLinkNr" . $obj->id] = $treeElementCount;
        $treeElements .= addToTree("Zust�nde", 0, "editGroupStates.php?groupId=" . $obj->id);
        $treeElements .= $closeTreeFolder;
        
        if ($hasSub[$obj->id] == 1)
        {
          foreach ( $subs as $groupObj )
          {
            if ($groupObj->subOf == $obj->id)
            {
              $treeElements .= addToTree("Subgruppe: " . $groupObj->name, 1, "editGroup.php?id=" . $groupObj->id);
              $treeElements .= addToTree("Basisregeln", 0, "editBaseConfig.php?groupId=" . $groupObj->id);
              $treeElements .= addToTree("Zusatzregeln", 1, "editRules.php?groupId=" . $groupObj->id);
              $_SESSION["groupLinkNr" . $groupObj->id] = $treeElementCount;
              $treeElements .= addToTree("Zust�nde", 0, "editGroupStates.php?groupId=" . $groupObj->id);
              $treeElements .= $closeTreeFolder;
              $treeElements .= $closeTreeFolder;
            }
          }
        }
      }
      else
      {
        $treeElements .= addToTree("Signalkonfiguration", 0, "editLogicalSignals.php?groupId=" . $obj->id);
        $treeElements .= addToTree("Zusatzregeln", 0, "editRules.php?groupId=" . $obj->id);
        $_SESSION["groupLinkNr" . $obj->id] = $treeElementCount;
      }
      $treeElements .= $closeTreeFolder;
    }
    $treeElements .= $closeTreeFolder;
    
    $html = str_replace("%TREE_ELEMENTS%", $treeElements, $html);
  }
  else
  {
    $html = loadTemplate("frame_frames_design.html");
  }
  
  if ($message != "")
  {
    $messageTag = getTag("%MESSAGE%", $html);
    $messageTag = str_replace("%MESSAGE%", $message, $messageTag);
    $html = str_replace("%MESSAGE%", $messageTag, $html);
  }
  else
    removeTag("%MESSAGE%", $html);
  
  if ($content != "")
    $content = loadTemplate($content);
  $html = str_replace("%CONTENT%", $content, $html);
}

function checkUniqueName($name)
{
  global $usedNames;
  
  if ($usedNames[$name] == 1)
  {
    $rand = rand(0, 10000);
    return checkUniqueName($name . "Bitte �ndern" . $rand);
  }
  return $name;
}

function addToTree($label, $isFolder, $link = "", $target = "", $isControllerOnline = "-1", $instanceId = "", $checked = "-1")
{
  global $treeElementCount;
  global $lastOpen;
  
  if ($treeElementCount == "") $treeElementCount = 0;
  if ($target == "") $target = "main";
  
  $class = "";
  if ($treeElementCount == $lastOpen) $class = "style='background-color:eeeeee'";
  $result = "<li ID='$treeElementCount' $class>";
  
  if ($isControllerOnline == 1) $result .= "<img src='img/online2.gif' title='online'> ";
  else if ($isControllerOnline == 0) $result .= "<img src='img/offline2.gif' title='offline'> ";
  
  if ($checked == 0) $result .= "<img src='img/removeSmall.gif' title='Zum L�schen markiert'> ";
  
  if ($link != "")
  {
    if (strpos($link, "?") !== FALSE) $link .= "&lastOpen=" . $treeElementCount . "&isFolder=$isFolder";
    else $link .= "?lastOpen=" . $treeElementCount . "&isFolder=$isFolder";
    
    if ($instanceId != "") $addInstance = "draggable=\"true\" ondragstart=\"event.dataTransfer.setData('text/plain', '$instanceId')\" ";
      
    $addHighlight = "onclick=\"highlight('$treeElementCount');\"";
    
    $result .= "<a href=\"$link\" $addInstance $addHighlight target=\"$target\">$label</a>&nbsp;&nbsp;";
  }
  else $result .= $label;
  
  if ($isFolder == 1) $result .= "<ul>";
  else $result .= "</li>";
  $result .= "\n";
  
  $treeElementCount++;
  
  return $result;
}

function readViewMode()
{
	$erg = QUERY("select paramValue from basicconfig where paramKey='view' limit 1");
  if ($row = MYSQL_FETCH_ROW($erg)) $ansicht = $row[0];
  else
  {
    QUERY("INSERT into basicconfig (paramKey,paramValue) values('view','Standard')");
    $ansicht = "Standard";
  }
    
  $_SESSION["ansicht"]=$ansicht;
  return $ansicht;
}

function readFreeDiskSpace()
{
	$free = disk_free_space("/");
	$ds = disk_total_space("/");
	
	$obj->prozent = round(100-($free*100/$ds));
	$obj->rest = round($free/1048576);
	
  return $obj;
}


?>
