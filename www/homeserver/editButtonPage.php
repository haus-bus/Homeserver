<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($action == "linkPage")
{
  if ($page > 0)
  {
    QUERY("UPDATE webapppagesbuttons set featureInstanceId='P$page' where id='$buttonId' limit 1");
    $action = "editButton";
  }
  else
  {
    setupTreeAndContent("choosePageLink_design.html");
    $html = str_replace("%PAGE_ID%", $pageId, $html);
    $html = str_replace("%BUTTON_ID%", $buttonId, $html);
    
    $pagesTag = getTag("%PAGES%", $html);
    $pages = "";
    $erg = QUERY("select id,name from webapppages where id!='$pageId' order by pos,name");
    while ( $obj = mysqli_fetch_OBJECT($erg) )
    {
      $actTag = $pagesTag;
      $actTag = str_replace("%PAGE%", $obj->id, $actTag);
      $actTag = str_replace("%PAGE_NAME%", $obj->name, $actTag);
      $pages .= $actTag;
    }
    $html = str_replace("%PAGES%", $pages, $html);
    show();
  }
}

if ($action == "addObject")
{
  if ($submitted == 2)
  {
    $parts = explode(",",$_SESSION["addObjects"]);
    foreach($parts as $id)
    {
        echo $id."<br>";
    }
    
    echo "Kopieren: $instanceId";
    exit;
    
  }
  else if ($submitted == 1)
  {
    $_SESSION["addObjects"]="";
    $erg = QUERY("select id from featureInstances");
    while ( $obj = mysqli_fetch_OBJECT($erg) )
    {
      $act = "id" . $obj->id;
      $act = $$act;
      if ($act == 1) $_SESSION["addObjects"].=$obj->id.",";
    }
    if ($_SESSION["addObjects"]=="")
    {
      header("Location: editButtonPage.php?pageId=$pageId");
      exit;
    }
    
    setupTreeAndContent("addObject_design.html");
    
    $html = str_replace("Objekt zur Webpage hinzufügen","Nun das Objekt wählen von dem das Symbol übernommen werden soll",$html);
    
    removeTag("%OPT_STEP1%",$html);
    
    $html = str_replace("<iframe", "<iframe height=1", $html);
    
    $closeTreeFolder = "</ul></li> \n";
    
    $treeElements = "";
    $treeElements .= addToTree("<a href='editButtonPage.php?pageId=pageId'>Symbol von bestehendem Objekt wählen</a>", 1);
    $html = str_replace("%INITIAL_ELEMENT2%", "expandToItem('tree2','$treeElementCount');", $html);
    
    $svgInfos = getSvgInfos($pageId);
    $myObjects = $svgInfos->objects;
    
    $allActionClasses = readFeatureClassesThatSupportType("ACTION");
    
    unset($ready);
    $lastRoom = "";
    $erg = QUERY("select rooms.id as roomId, rooms.name as roomName,
                                 roomFeatures.featureInstanceId,
                                 featureInstances.name as featureInstanceName, featureInstances.featureClassesId, featureInstances.objectId as featureObjectId,
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
      if ($ready[$obj->featureInstanceId] == 1)
        continue;
    
      $ready[$obj->featureInstanceId] = 1;
    
      if ($myObjects[$obj->featureObjectId] != 1)
        continue;
    
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
    
          if ($myObjects[$obj->featureObjectId] == 1)
          {
            $checked = "checked";
            $assigned .= "expandToItem('tree2'," . $treeElementCount . ");\n";
          }
          else
            $checked = "";
    
          $treeElements .= addToTree("<a href='editButtonPage.php?pageId=$pageId&action=addObject&submitted=2&instanceId=$obj->featureInstanceId'>$obj->featureInstanceName</a>", 0);
        }
      }
    }
    
    $treeElements .= $closeTreeFolder; // letzte featureclass
    $treeElements .= $closeTreeFolder; // letzter raum
    
    
    $lastRoom = "";
    $lastController = "";
    $erg = QUERY("select featureInstances.id as featureInstanceId, featureInstances.name as featureInstanceName, featureInstances.featureClassesId, featureInstances.objectId as featureObjectId,
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
    
      if ($myObjects[$obj->featureObjectId] != 1)
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
    
          if ($myObjects[$obj->featureObjectId] == 1)
          {
            $checked = "checked";
            $assigned .= "expandToItem('tree2'," . $treeElementCount . ");\n";
          }
          else
            $checked = "";
    
          
          $treeElements .= addToTree("<a href='editButtonPage.php?pageId=$pageId&action=addObject&submitted=2&instanceId=$obj->featureInstanceId'>$obj->featureInstanceName</a>", 0);
        }
      }
    }
    
    $treeElements .= $closeTreeFolder; // letzte featureclass
    $treeElements .= $closeTreeFolder; // letzter controller
    $treeElements .= $closeTreeFolder; // letzter raum
    
    
    $html = str_replace("%TREE_ELEMENTS%", $treeElements, $html);
    $html = str_replace("%PAGE_ID%", $pageId, $html);
    $html = str_replace("%ASSIGNED%", $assigned, $html);
    
    show();
    
    exit;
  }
  else
  {
    setupTreeAndContent("addObject_design.html");
    
    chooseTag("%OPT_STEP1%",$html);
    
    $html = str_replace("<iframe", "<iframe height=1", $html);
    
    $closeTreeFolder = "</ul></li> \n";
    
    $treeElements = "";
    $treeElements .= addToTree("<a href='editButtonPage.php?pageId=pageId'>Neues Objekt für Webpage auswählen</a>", 1);
    $html = str_replace("%INITIAL_ELEMENT2%", "expandToItem('tree2','$treeElementCount');", $html);
    
    $svgInfos = getSvgInfos($pageId);
    $myObjects = $svgInfos->objects;
    
    $allActionClasses = readFeatureClassesThatSupportType("ACTION");
    
    unset($ready);
    $lastRoom = "";
    $erg = QUERY("select rooms.id as roomId, rooms.name as roomName,
                                 roomFeatures.featureInstanceId,
                                 featureInstances.name as featureInstanceName, featureInstances.featureClassesId, featureInstances.objectId as featureObjectId,
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
      if ($ready[$obj->featureInstanceId] == 1)
        continue;
      
      $ready[$obj->featureInstanceId] = 1;
      
      if ($myObjects[$obj->featureObjectId] == 1)
        continue;
      
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
          
          if ($myObjects[$obj->featureObjectId] == 1)
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
    $erg = QUERY("select featureInstances.id as featureInstanceId, featureInstances.name as featureInstanceName, featureInstances.featureClassesId, featureInstances.objectId as featureObjectId,
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

      if ($myObjects[$obj->featureObjectId] == 1)
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
          
          if ($myObjects[$obj->featureObjectId] == 1)
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
    $html = str_replace("%PAGE_ID%", $pageId, $html);
    $html = str_replace("%ASSIGNED%", $assigned, $html);
    
    show();
  }
}

if ($action == "editLine")
{
  setupTreeAndContent("editLine_design.html");
  if ($lineId != "")
  {
    $erg = QUERY("select name,pos from webapppageszeilen where id='$lineId' limit 1");
    $row = mysqli_fetch_ROW($erg);
    $lineName = $row[0];
    $pos = $row[1];
    $title = "Absatz ändern";
    $submitTitle = "Ändern";
    chooseTag("%OPT_DELETE%", $html);
  }
  else
  {
    $title = "Absatz erstellen";
    $submitTitle = "Erstellen";
    $pos = 1;
    removeTag("%OPT_DELETE%", $html);
  }
  
  $html = str_replace("%TITLE%", $title, $html);
  $html = str_replace("%SUBMIT_TITLE%", $submitTitle, $html);
  $html = str_replace("%LINE_NAME%", $lineName, $html);
  $html = str_replace("%PAGE_ID%", $pageId, $html);
  $html = str_replace("%LINE_ID%", $lineId, $html);
  $html = str_replace("%POS%", $pos, $html);
  show();
}

if ($action == "submitLine")
{
  if ($delete == 1)
  {
    QUERY("DELETE from webapppageszeilen where id='$lineId' limit 1");
    QUERY("DELETE from webapppagesbuttons where zeilenId='$lineId'");
  }
  else if ($lineId == "")
  {
    QUERY("INSERT into webapppageszeilen (pageId, name,pos) values('$pageId','$name','$pos')");
    $lineId = query_insert_id();
  }
  else
    QUERY("UPDATE webapppageszeilen set name='$name',pos='$pos' where id='$lineId' limit 1");
}

if ($action == "editButton")
{
  if ($featureInstanceId != "") QUERY("UPDATE webapppagesbuttons set featureInstanceId='$featureInstanceId' where id='$buttonId' limit 1");
  
  setupTreeAndContent("editButton_design.html");
  if ($buttonId != "")
  {
    $erg = QUERY("select name,pos,featureInstanceId from webapppagesbuttons where id='$buttonId' limit 1");
    $row = mysqli_fetch_ROW($erg);
    $buttonName = $row[0];
    $pos = $row[1];
    $link = $row[2];
    if (substr($link, 0, 1) == "P")
    {
      $page = substr($link, 1);
      $erg = QUERY("select name from webapppages where id='$page' limit 1");
      if ($row = mysqli_fetch_ROW($erg)) $signal = "Subseite: $row[0]";
      else $signal = "Fehler: Subseite nicht mehr vorhanden";
    }
    else if (strpos($link, "http://") !== FALSE) $signal = $link;
    else
    {
      // FeatureInstance suchen
      $erg = QUERY("select controllerId, name from featureInstances where id='$link' limit 1");
      $row = mysqli_fetch_ROW($erg);
      $controllerId = $row[0];
      $instanceName = $row[1];
      
      $header = "";
      $erg = QUERY("select roomId from roomFeatures where featureInstanceId='$link' limit 1");
      if ($row = mysqli_fetch_ROW($erg))
      {
        $erg = QUERY("select name from rooms where id='$row[0]' limit 1");
        $row = mysqli_fetch_ROW($erg);
        $header = $row[0];
      }
      else
      {
        $erg = QUERY("select name from controller where id='$controllerId' limit 1");
        $row = mysqli_fetch_ROW($erg);
        $header = "Controller " . $row[0];
      }
      
      $signal = $header .= " » " . $instanceName;
    }
    $title = "Button ändern";
    $submitTitle = "Ändern";
    chooseTag("%OPT_DELETE%", $html);
  }
  else
  {
    $title = "Button erstellen";
    $submitTitle = "Erstellen";
    $pos = "1";
    $signal = "";
    removeTag("%OPT_DELETE%", $html);
  }
  
  $html = str_replace("%TITLE%", $title, $html);
  $html = str_replace("%SUBMIT_TITLE%", $submitTitle, $html);
  $html = str_replace("%BUTTON_NAME%", $buttonName, $html);
  $html = str_replace("%PAGE_ID%", $pageId, $html);
  $html = str_replace("%LINE_ID%", $lineId, $html);
  $html = str_replace("%BUTTON_ID%", $buttonId, $html);
  $html = str_replace("%POS%", $pos, $html);
  $html = str_replace("%SIGNAL%", $signal, $html);
  show();
}

if ($action == "submitButton" || $action == "chooseSignal" || $action == "choosePage")
{
  if ($delete == 1) QUERY("DELETE from webapppagesbuttons where id='$buttonId'");
  else if ($buttonId == "")
  {
    if (strpos($signal, "http://") !== FALSE)
      QUERY("INSERT into webapppagesbuttons (zeilenId, name,pos,featureInstanceId) values('$lineId','$name','$pos','$signal')");
    else
      QUERY("INSERT into webapppagesbuttons (zeilenId, name,pos) values('$lineId','$name','$pos')");
    $buttonId = query_insert_id();
  }
  else
  {
    if (strpos($signal, "http://") !== FALSE)
      QUERY("UPDATE webapppagesbuttons set name='$name',pos='$pos',featureInstanceId='$signal' where id='$buttonId' limit 1");
    else
      QUERY("UPDATE webapppagesbuttons set name='$name',pos='$pos' where id='$buttonId' limit 1");
  }
  
  if ($action == "chooseSignal")
  {
    header("Location: editRules.php?action=editButton&buttonId=$buttonId&pageId=$pageId");
    exit();
  }
  
  if ($action == "choosePage")
  {
    header("Location: editButtonPage.php?action=linkPage&buttonId=$buttonId&pageId=$pageId");
    exit();
  }
}

if ($submitted != "")
{
  if ($delete == 1 && $pageId > 0)
  {
    QUERY("DELETE from webappPages where id='$pageId' limit 1");
    $erg = QUERY("select id from webapppageszeilen where pageId='$pageId'");
    while ( $row = mysqli_fetch_ROW($erg) )
    {
      $actId = $row[0];
      QUERY("DELETE from webapppageszeilen where id='$actId' limit 1");
      QUERY("DELETE from webapppagesbuttons where zeilenId='$actId'");
    }
    
    @unlink("webapp/".$pageId.".webapp");
    
    triggerTreeUpdate();
    header("Location: editButtonPage.php");
    exit();
  }
  else
  {
    if ($pageId == "")
    {
      if ($startseite == 1)
      {
        $pos = 1;
        QUERY("UPDATE webappPages set pos='2'");
      }
      
      QUERY("INSERT into webappPages (name,pos) values('$name','$pos')");
      $pageId = query_insert_id();
    }
    else
    {
      if ($startseite == 1)
      {
        $pos = 1;
        QUERY("UPDATE webappPages set pos='2'");
      }
      
      QUERY("UPDATE webappPages set name='$name',pos='$pos' where id='$pageId' limit 1");
      $message = "Einstellungen gespeichert";
    }
    
    triggerTreeUpdate();
    
    $orig = $_FILES['userfile']['name'];
    if ($orig != "")
    {
      $filename = $pageId . ".webapp";
      generatePage($_FILES['userfile']['tmp_name'], "webapp/$filename");
      QUERY("UPDATE webappPages set filename='$filename' where id='$pageId' limit 1");
      
      die("<script>location='editButtonPage.php?action=checkPage&pageId=$pageId';</script>");
    }
  }
}

setupTreeAndContent("editButtonPage_design.html");

if ($pageId == "")
{
  $html = str_replace("%PAGE_ID%", "", $html);
  $html = str_replace("%TITLE%", "Neue Seite in der Webapplikation anlegen", $html);
  $html = str_replace("%TITLE2%", "URL der Webapplikation: <a href='webapp/' target='_blank'>http://".$_SERVER["HTTP_HOST"]."/homeserver/webapp</a>", $html);
  $html = str_replace("%SUBMIT_TITLE%", "Seite erstellen", $html);
  $html = str_replace("%PAGE_NAME%", "", $html);
  
  $erg = QUERY("select id from webappPages where pos='1' limit 1");
  if ($row = mysqli_fetch_ROW($erg)) { }
  else  $checked = "checked";
  $html = str_replace("%CHECKED%", $checked, $html);
  $html = str_replace("%SVG_FUNCTIONS%", $checked, $html);
  removeTag("%OPT_DELETE%", $html);
  removeTag("%OPT_CONFIG%", $html);
}
else
{
  $html = str_replace("%PAGE_ID%", $pageId, $html);
  $html = str_replace("%TITLE%", "Seite bearbeiten", $html);
  $html = str_replace("%SUBMIT_TITLE%", "Ändern", $html);
  chooseTag("%OPT_DELETE%", $html);
  chooseTag("%OPT_CONFIG%", $html);
  
  $svgInfos = getSvgInfos($pageId);
  if ($svgInfos == - 1)
    $html = str_replace("%SVG_FUNCTIONS%", "", $html);
  else
  {
    $info = $svgInfos->ok . " funktionierende Objekte gefunden <br>";
    if ($svgInfos->old > 0)
      $info .= $svgInfos->old . " alte nicht mehr gültige Objekte gefunden <br>";
    if ($svgInfos->notFound > 0)
      $info .= $svgInfos->notFound . " ungültige nicht existierende Objekte gefunden <br>";
    //$info .= "<br><br><a href='editButtonPage.php?pageId=$pageId&action=addObject'><u>Weitere Objekte zur Seite hinzufügen</a><br>";
    $html = str_replace("%SVG_FUNCTIONS%", $info, $html);
  }
  
  $erg = QUERY("select name,pos from webappPages where id='$pageId' limit 1");
  if ($obj = mysqli_fetch_OBJECT($erg))
  {
    $html = str_replace("%PAGE_NAME%", $obj->name, $html);
    if ($obj->pos == 1)
    {
      $checked = "checked";
      $html = str_replace("%TITLE2%", "URL der Webapplikation: <a href='webapp/' target='_blank'>http://".$_SERVER["HTTP_HOST"]."/homeserver/webapp</a>", $html);
    }
    else $html = str_replace("%TITLE2%", "URL dieser Unterseite: <a href='webapp/?id=$pageId' target='_blank'>http://".$_SERVER["HTTP_HOST"]."/homeserver/webapp/?id=$pageId"."</a>", $html);

    $html = str_replace("%CHECKED%", $checked, $html);
  }
  else
    die("FEHLER! Ungültige ID $pageId");
  
  $zeilenTag = getTag("%OPT_ZEILEN%", $html);
  $zeilen = "";
  $buttonTag = getTag("%OPT_BUTTONS%", $zeilenTag);
  
  $erg = QUERY("select id, name from webapppageszeilen where pageId='$pageId' order by pos");
  while ( $obj = mysqli_fetch_OBJECT($erg) )
  {
    $actTag = $zeilenTag;
    if ($obj->name == "")
      $obj->name = "-Absatz ohne Name-";
    $actTag = str_replace("%ZEILE%", $obj->name, $actTag);
    $actTag = str_replace("%LINE_ID%", $obj->id, $actTag);
    
    $buttons = "";
    $erg2 = QUERY("select id, name from webapppagesbuttons where zeilenId='$obj->id' order by pos");
    while ( $obj2 = mysqli_fetch_OBJECT($erg2) )
    {
      $actButtonTag = $buttonTag;
      $actButtonTag = str_replace("%BUTTON_ID%", $obj2->id, $actButtonTag);
      $actButtonTag = str_replace("%BUTTON_NAME%", $obj2->name, $actButtonTag);
      $buttons .= $actButtonTag;
    }
    $actTag = str_replace("%OPT_BUTTONS%", $buttons, $actTag);
    
    $zeilen .= $actTag;
  }
  
  $html = str_replace("%OPT_ZEILEN%", $zeilen, $html);
}

show();

function generatePage($fileIn, $fileOut)
{
	$input = file_get_contents($fileIn);

  if (strpos($input, 'id="__x0022_') !== FALSE)
    generateSvgCorel($fileIn, $fileOut);
  else
    generateSvgOther($fileIn, $fileOut);
}

function generateSvgCorel($fileIn, $fileOut)
{
  $input = file_get_contents($fileIn);
  
  // SVG ausschneiden
  $pos = strpos($input, "<svg");
  if ($pos === FALSE)
    die("Fehler: <svg nicht gefunden");
  $pos2 = strpos($input, "</svg>", $pos);
  if ($pos2 === FALSE)
    die("Fehler: </svg> nicht gefunden");
  $input = substr($input, $pos, $pos2 + 6 - $pos);
  
  // Links einbauen
  $errorCounter = 0;
  $pos = 0;
  while ( $errorCounter < 1000 )
  {
    $errorCounter++;
    
    $pos = strpos($input, '<odm name="Type" value="link"', $pos);
    if ($pos === FALSE)
      break;
    $pos2 = strrpos(substr($input, 0, $pos), '<odm name="action" value="');
    if ($pos2 === FALSE)
      break;
    $pos3 = strpos($input, '"', $pos2 + 26);
    if ($pos3 === FALSE)
      break;
    
    $link = substr($input, $pos2 + 26, $pos3 - $pos2 - 26);
    
    $pos2 = strrpos(substr($input, 0, $pos), '<g>');
    if ($pos2 === FALSE)
      break;
    
    $input = substr($input, 0, $pos2) . "<a xlink:href='$link'>" . substr($input, $pos2);
    
    $pos3 = strpos($input, '</g>', $pos2);
    if ($pos3 === FALSE)
      break;
    $input = substr($input, 0, $pos3 + 4) . "</a>" . substr($input, $pos3 + 4);
    
    $pos2 = strpos($input, '<metadata', $pos2);
    if ($pos2 === FALSE)
      break;
    $pos3 = strpos($input, '</metadata', $pos2);
    if ($pos3 === FALSE)
      break;
    
    $input = substr($input, 0, $pos2) . substr($input, $pos3 + 11);
    
    $pos = 0;
  }
  
  $input = str_replace("_x0023_","#",$input);
  
  // Objekte und Typen suchen
  // id="__x0022_100995845_x0022_"
  // <odm name="Type" value="switch" type="0"/>
  // Status und Text IDs ergänzen
  // id="status_4"
  // id="text_5"
  $errorCounter = 0;
  while ( $errorCounter < 1000 )
  {
    $errorCounter++;
    
    $pos = strpos($input, 'id="__x0022_');
    if ($pos === FALSE)
      break;
    
    $next = strpos($input, 'id="__x0022_', $pos + 10);
    if ($next === FALSE)
      $next = strlen($input);
    
    $pos2 = strpos($input, '_x0022_', $pos + 12);
    $id = substr($input, $pos + 12, $pos2 - $pos - 12);

    $myIds[$id] = 1;
    
    $pos3 = strpos($input, '<odm name="Type" value="', $pos2);
    if ($pos3 === FALSE)
      die("Fehler: Type nicht gefunden bei ID $id");
    $pos4 = strpos($input, '"', $pos3 + 24);
    $type = substr($input, $pos3 + 24, $pos4 - $pos3 - 24);
    $myTypes[$id] = $type;
    
    $pos3 = strpos($input, '<odm name="action" value="', $pos2);
    if ($pos3 !== FALSE && $pos3 < $next)
    {
      $pos4 = strpos($input, '"', $pos3 + 26);
      $action = substr($input, $pos3 + 26, $pos4 - $pos3 - 26);
      $myAction[$id] = $action;
    }
    
    $pos3 = strpos($input, '<odm name="param1" value="', $pos2);
    if ($pos3 !== FALSE && $pos3 < $next)
    {
      $pos4 = strpos($input, '"', $pos3 + 26);
      $param1 = substr($input, $pos3 + 26, $pos4 - $pos3 - 26);
      $myParam1[$id] = $param1;
    }
    
    $pos3 = strpos($input, '<odm name="param2" value="', $pos2);
    if ($pos3 !== FALSE && $pos3 < $next)
    {
      $pos4 = strpos($input, '"', $pos3 + 26);
      $param2 = substr($input, $pos3 + 26, $pos4 - $pos3 - 26);
      $myParam2[$id] = $param2;
    }
    
    $pos3 = strpos($input, '<odm name="function" value="', $pos2);
    if ($pos3 !== FALSE && $pos3 < $next)
    {
      $pos4 = strpos($input, '"', $pos3 + 28);
      $function = substr($input, $pos3 + 28, $pos4 - $pos3 - 28);
      $myFunction[$id] = $function;
    }

    $pos3 = strpos($input, '<odm name="functionParam1" value="', $pos2);
    if ($pos3 !== FALSE && $pos3 < $next)
    {
      $pos4 = strpos($input, '"', $pos3 + 34);
      $functionParam1 = substr($input, $pos3 + 34, $pos4 - $pos3 - 34);
      $myFunctionParam1[$id] = $functionParam1;
    }

    $pos3 = strpos($input, '<odm name="functionParam2" value="', $pos2);
    if ($pos3 !== FALSE && $pos3 < $next)
    {
      $pos4 = strpos($input, '"', $pos3 + 34);
      $functionParam2 = substr($input, $pos3 + 34, $pos4 - $pos3 - 34);
      $myFunctionParam2[$id] = $functionParam2;
    }
    
    $input = substr($input, 0, $pos + 4) . substr($input, $pos + 12);
    
    $pos3 = strpos($input, 'id="status', $pos + 4);
    $pos4 = strpos($input, '"', $pos3 + 10);
    
    $input = substr($input, 0, $pos3 + 10) . $id . substr($input, $pos4);
    
    $pos3 = strpos($input, 'id="text', $pos + 4);
    $pos4 = strpos($input, '"', $pos3 + 8);
    $input = substr($input, 0, $pos3 + 8) . $id . substr($input, $pos4);
    
    $pos3 = strpos($input, 'id="arrowUp', $pos + 4);
    if ($pos3 !== FALSE && $pos3 < $next)
    {
      $pos4 = strpos($input, '"', $pos3 + 11);
      $input = substr($input, 0, $pos3 + 11) . $id . substr($input, $pos4);
    }
    
    $pos3 = strpos($input, 'id="arrowDown', $pos + 4);
    if ($pos3 !== FALSE && $pos3 < $next)
    {
      $pos4 = strpos($input, '"', $pos3 + 13);
      $input = substr($input, 0, $pos3 + 13) . $id . substr($input, $pos4);
    }
  }
  
  // Anführungszeichen filtern
  $input = str_replace("_x0022_", "", $input);
  
  // Metadaten filtern
  // <metadata </metadata>
  $errorCounter = 0;
  while ( $errorCounter < 1000 )
  {
    $pos = strpos($input, '<metadata');
    if ($pos === FALSE)
      break;
    $pos2 = strpos($input, '>', $pos);
    $before = substr($input, $pos2 - 1, 1);
    if ($before == "/")
      $pos2 += 1;
    else
      $pos2 = strpos($input, '</metadata', $pos) + 11;
    
    $pos2 += 2; // Zeilenumbruch mit abschneiden
    

    //$cut = substr($input,$pos,$pos2-$pos);
    //echo htmlentities($cut)."<hr>";
    

    $input = substr($input, 0, $pos) . substr($input, $pos2 + $length);
  }
  
  // Leerzeichen filtern
  $input = str_replace("    ", "", $input);
  $input = str_replace("   ", "", $input);
  $input = str_replace("  ", "", $input);
  
  // Blödsinn filtern
  $errorCounter = 0;
  while ( $errorCounter < 1000 )
  {
    $errorCounter++;
    $pos = strpos($input, "sodipodi");
    if ($pos === FALSE)
      break;
    $pos2 = strpos($input, '"', $pos + 1);
    $pos2 = strpos($input, '"', $pos2 + 1);
    $input = substr($input, 0, $pos) . substr($input, $pos2 + 1);
  }
  
  // Transparenz der Rollopfeile umbauen
  $input = str_replace("fill-opacity:0.000000", "opacity:0", $input);
  
  // Slider einbauen
  $pos = strpos($input, "<path id=\"slideup\"");
  if ($pos === FALSE)
    echo "Achtung: Kein Slider gefunden (ID: slideup)";
  else
  {
    $pos = strpos($input, "/>", $pos);
    $input = substr($input, 0, $pos + 2) . "</g>" . substr($input, $pos + 2);
    $input = str_replace("<path id=\"slideup\"", "<g id=\"slideup\" visibility=\"hidden\"><path", $input);
  }
  
  $pos = strpos($input, "<path id=\"slidedown\"");
  if ($pos === FALSE)
    echo "Achtung: Kein Slider gefunden (ID: slidedown)";
  else
  {
    $pos = strpos($input, "/>", $pos);
    $input = substr($input, 0, $pos + 2) . "</g>" . substr($input, $pos + 2);
    $input = str_replace("<path id=\"slidedown\"", "<g id=\"slidedown\" visibility=\"hidden\"><path", $input);
  }
  
  //$input = str_replace("</svg>",'<g id="slideup" visibility="hidden"><path style="stroke:#1F1A17;stroke-width:0.1764;fill:#DA251D;fill-rule:nonzero" d="M28.5062 21.5064l0.0002 0 6.1735 6.8199 -12.3472 0 6.1735 -6.8199zm0 0m3.087 3.41m-3.087 3.4099m-3.0868 -3.4099"/></g>'."\n".'<g id="slidedown" visibility="hidden"><path style="stroke:#1F1A17;stroke-width:0.1764;fill:#00923F;fill-rule:nonzero" d="M28.5062 37.9264l0.0002 0 6.1735 -6.8199 -12.3472 0 6.1735 6.8199zm0 0m3.087 -3.41m-3.087 -3.4099m-3.0868 3.4099"/></g>'."\n".'</svg>',$input);
  

  $output = file_get_contents("appTemplate.tpl");
  
  // SVG ins Template einfügen
  // #SVG#
  $output = str_replace("#SVG#", $input, $output);
  
  // Objektregistrierung erstellen
  // #REGISTER_OBJECTS#
  // registerObject("1493176580","dimmer","fill","yellow","white");
  $register = "";
  foreach ( $myIds as $id => $dummy )
  {
    $action = $myAction[$id];
    $param1 = $myParam1[$id];
    $param2 = $myParam2[$id];
    $function = $myFunction[$id];
    $functionParam1 = $myFunctionParam1[$id];
    $functionParam2 = $myFunctionParam2[$id];
    
    if (($myTypes[$id] == "dimmer" || $myTypes[$id] == "switch" || $myTypes[$id] == "led") && $action == "")
    {
      $action = "fill";
      $param1 = "yellow";
      $param2 = "white";
    }
    $register .= 'registerObject("' . $id . '","' . $myTypes[$id] . '","' . $action . '","' . $param1 . '","' . $param2 . '","' . $functionParam1 . '","' . $functionParam2 . '","' . $function. '");' . "\n";
  }
  
  $output = str_replace("#REGISTER_OBJECTS#", $register, $output);
  $output = str_replace("<svg", '<svg preserveAspectRatio="xMidYMin"', $output);
  
  echo count($myIds) . " Objekte gefunden<br>";
  file_put_contents($fileOut, $output);
}

function generateSvgOther($fileIn, $fileOut)
{
  $register = "";
  $objectTypes[16] = "movement";
  $objectTypes[17] = "dimmer";
  $objectTypes[18] = "rollo";
  $objectTypes[19] = "switch";
  $objectTypes[21] = "led";
  $objectTypes[32] = "temperature";
  $objectTypes[34] = "humidity";
  
  $svg = simplexml_load_file($fileIn);
  foreach ( $svg->g as $g0 )
  {
    foreach ( $g0->attributes() as $a => $b )
    {
      if ($a == "id" && ! substr_compare($b, "HAS_ID_", 0, 7))
      {
        $objectId = substr($b, 7);
        
        $type = $objectTypes[((int)$objectId & 0xFF00) >> 8];
        
        $g0->attributes()->id = $objectId;
        $g0->text->attributes()->id = "text" . $objectId;
        foreach ( $g0->path as $p )
        {
          if (! substr_compare($p->attributes()->id, "status", 0, 6))
            $p->attributes()->id = "status" . $objectId;
          else if (! substr_compare($p->attributes()->id, "arrowDown", 0, 9))
            $p->attributes()->id = "arrowDown" . $objectId;
          else if (! substr_compare($p->attributes()->id, "arrowUp", 0, 7))
            $p->attributes()->id = "arrowUp" . $objectId;
        }
		foreach ( $g0->rect as $r )
        {
          if (! substr_compare($r->attributes()->id, "status", 0, 6))
            $r->attributes()->id = "status" . $objectId;
        }
        $action = "";
        $param1 = "";
        $param2 = "";
        $function = "";
        $functionParam1 = "";
        $functionParam2 = "";
        $otherObjectId = "";
        $otherAction = "";
        $otherParamOn = "";
        $otherParamOff = "";
        $otherParam1 = "";
        $otherObjectId2 = "";
        $otherAction2 = "";
        $otherParamOn2 = "";
        $otherParamOff2 = "";
        $otherParam12 = "";
        
        if (($type == "dimmer" || $type == "switch" || $type == "led"))
        {
          $action = "fill";
          $param1 = "yellow";
          $param2 = "white";
        }
        else if ($type == "rollo") // besser shutter
        {
          $action = "opacity";
        }
        else if ($type == "movement")
        {
          $action = "fill";
          $param1 = "palevioletred";
          $param2 = "silver";
        }
        
        if ($g0->metadata)
        {
          foreach ( $g0->metadata->attributes() as $a => $b )
          {
            if ($a == 'action') $action = $b;
            if ($a == 'param1') $param1 = $b;
            if ($a == 'param2') $param2 = $b;
            if ($a == 'function') $function = $b;
            if ($a == 'functionParam1') $functionParam1 = $b;
            if ($a == 'functionParam2') $functionParam2 = $b;
            if ($a == 'otherObjectId') $otherObjectId = $b;
            if ($a == 'otherAction') $otherAction = $b;
            if ($a == 'otherParamOn') $otherParamOn = $b;
            if ($a == 'otherParamOff') $otherParamOff = $b;
            if ($a == 'otherParam1') $otherParam1 = $b;
            if ($a == 'otherObjectId2') $otherObjectId2 = $b;
            if ($a == 'otherAction2') $otherAction2 = $b;
            if ($a == 'otherParamOn2') $otherParamOn2 = $b;
            if ($a == 'otherParamOff2') $otherParamOff2 = $b;
            if ($a == 'otherParam12') $otherParam12 = $b;
          }
        }
        else
        {
          $g0->addChild('metadata');
          $md = $g0->metadata;
          $md->addAttribute('id', 'meta' . $objectId);
          $md->addAttribute('type', $type);
          $md->addAttribute('action', $action);
          $md->addAttribute('param1', $param1);
          $md->addAttribute('param2', $param2);
          $md->addAttribute('function', $function);
          $md->addAttribute('functionParam1', $functionParam1);
          $md->addAttribute('functionParam2', $functionParam2);
          $md->addAttribute('otherObjectId', $otherObjectId);
          $md->addAttribute('otherAction', $otherAction);
          $md->addAttribute('otherParamOn', $otherParamOn);
          $md->addAttribute('otherParamOff', $otherParamOff);
          $md->addAttribute('otherParam1', $otherParam1);
          $md->addAttribute('otherObjectId2', $otherObjectId2);
          $md->addAttribute('otherAction2', $otherAction2);
          $md->addAttribute('otherParamOn2', $otherParamOn2);
          $md->addAttribute('otherParamOff2', $otherParamOff2);
          $md->addAttribute('otherParam12', $otherParam12);
        }
        $register .= 'registerObject("' . $objectId . '","' . $type . '","' . $action . '","' . $param1 . '","' . $param2 . '","' . $functionParam1 . '","' . $functionParam2 . '","' . $function .  '","' . $otherObjectId . '","' . $otherAction . '","' . $otherParamOn . '","' . $otherParamOff . '","' . $otherParam1 . '","' .  $otherObjectId2 . '","' . $otherAction2 . '","' . $otherParamOn2 . '","' . $otherParamOff2 . '","' . $otherParam12 . '");' . "\n";
        //echo $b, " registered as ", $type, "\n";          
      }
    }
  }
  // file_put_contents($fileOut.'meta.svg',$svg->asXml());
  
  for($i = 0; $i < count($svg->g); $i++)
  {
    unset($svg->g[$i]->metadata);
  }
  $output = file_get_contents("appTemplate.tpl");
  // SVG ins Template einfügen
  // #SVG#
  $output = str_replace("#SVG#", $svg->asXml(), $output);
  $output = str_replace("#REGISTER_OBJECTS#", $register, $output);
  $output = str_replace("</svg>", '<g id="slideup" visibility="hidden"><path style="stroke:#1F1A17;stroke-width:0.1764;fill:#DA251D;fill-rule:nonzero" d="M28.5062 21.5064l0.0002 0 6.1735 6.8199 -12.3472 0 6.1735 -6.8199zm0 0m3.087 3.41m-3.087 3.4099m-3.0868 -3.4099"/></g>' . "\n" . '<g id="slidedown" visibility="hidden"><path style="stroke:#1F1A17;stroke-width:0.1764;fill:#00923F;fill-rule:nonzero" d="M28.5062 37.9264l0.0002 0 6.1735 -6.8199 -12.3472 0 6.1735 6.8199zm0 0m3.087 -3.41m-3.087 -3.4099m-3.0868 3.4099"/></g>' . "\n" . '</svg>', $output);
  $output = str_replace("<svg", '<svg preserveAspectRatio="xMidYMin"', $output);
  file_put_contents($fileOut, $output);
}

function getSvgInfos($pageId)
{
  $file = "webapp/" . $pageId . ".webapp";
  if (file_exists($file))
  {
    $svg = file_get_contents($file);
    $pos = - 1;
    $errorCounter = 0;
    $notFound = "";
    $oldObjects = "";
    $nrObjects = 0;
    while ( $pos !== FALSE && $errorCounter < 1000 )
    {
      $errorCounter++;
      $pos = strpos($svg, "registerObject(", $pos + 1);
      if ($pos === FALSE)
        break;
      $pos = strpos($svg, '"', $pos);
      $pos2 = strpos($svg, '"', $pos + 4);
      $objectId = substr($svg, $pos + 1, $pos2 - $pos - 1);
      
      $pos = strpos($svg, '"', $pos2 + 1);
      $pos2 = strpos($svg, '"', $pos + 4);
      $type = substr($svg, $pos + 1, $pos2 - $pos - 1);
      
      $nrObjects++;
      
      $erg = QUERY("select checked from featureinstances where objectId='$objectId' limit 1");
      if ($row = mysqli_fetch_ROW($erg))
      {
        if ($row[0] != 1)
        {
          if ($oldObjects != "")
            $oldObjects .= ", ";
          $oldObjects .= $objectId . " ($type)";
        }
        else
          $svgObjects[$objectId] = 1;
      }
      else
      {
        if ($notFound != "")
          $notFound .= ", ";
        $notFound .= $objectId . " ($type)";
      }
    }
    
    $result->notFound = $notFound;
    $result->old = $oldObjects;
    $result->ok = $nrObjects;
    $result->objects = $svgObjects;
    return $result;
  }
  else
    return - 1;
}
?>
