<?php
include ($_SERVER ["DOCUMENT_ROOT"] . "/homeserver/include/all.php");
//header ( 'Content-Encoding: none; ' ); // disable apache compressed

if ($action == "recoverDb") recoverDb();
  
if ($forward==1)
{
	 foreach($_SESSION["forwardGet"] as $key=>$value)
	 {
	 	  $$key=$value;
	 }
}

if ($proceed == 1)
{
	if ($_SESSION ["onlineVersionMainController"]=="")
	{
		$_SESSION["forwardGet"]=$_GET;
		header("Location: updates.php?action=readOnlineVersion&forward=updatesProceed.php");
		exit;
	}
	
  if ($action == "update") // Webapplication
  {
    /*
     * $heute = date("d_m_Y_H_i_s"); @mkdir("../backups"); @mkdir("../backups/backup_$heute"); echo "<li> Backup wird erstellt (backup_$heute)..."; if (!file_exists("../backups/backup_$heute")) echo "Achtung: Backupverzeichnis wurde nicht erstellt<br>"; flush();ob_flush(); $result = backup("../homeserver","../backups/backup_$heute/apl.zip"); if (!$result) die("Backup Teil 1 fehlgeschlagen"); /*$result = backup("../../mysql/data/homeserver","../backups/backup_$heute/db.zip"); if (!$result) die("Backup Teil 2 fehlgeschlagen");
     */
    // echo "OK<br>";
    
    echo "<li> Neue Softwareversion wird runtergeladen...";
    flushIt ();
    $result = download ( "http://www.haus-bus.de/apl.zip", "../apl.zip" );
    if (! $result)
      die ( "Download Teil 1 fehlgeschlagen" );
      /*
     * $result = download("http://www.haus-bus.de/db.zip","../db.zip"); if (!$result) die("Download Teil 2 fehlgeschlagen");
     */
    echo "OK<br>";
    
    echo "<li> Neue Softwareversion wird installiert...";
    flushIt ();
    // @unlink("dbUpdate.php");
    umask ( 0 );
    error_reporting ( E_ERROR );
    for($i = 0; $i < 10; $i ++)
    {
      $result = unzip ( "../apl.zip" );
      if ($result)
        break;
      if ($i == 2)
        error_reporting ( 0 );
      if ($i == 9)
        die ( "Installation fehlgeschlagen" );
    }
    echo "OK<br><br>";

    echo "<li> Datenbank wird aktualisiert ";
    flushIt ();
    updateDatabase();

    if (file_exists("mainIndex.html"))
    {
    	exec("cp mainIndex.html ../index.html");
    	unlink("mainIndex.html");
    }
    
    
    if (file_exists("my.cnf")) $dbConfigUpdate=1;
    
    $erg = QUERY("select id from featureInstances where objectId='800981249' limit 1");
    if ($obj=MYSQLi_FETCH_OBJECT($erg)) callObjectMethodByName ( 800981249, "quit", "" );
    else echo "WARNUNG: Der Raspberry wurde noch nicht im System gefunden, vor dem Einspielen eines Backups muss der Controllerstatus aktualisiert und der Raspberry neu gestartet werden! <br>";
    
    if ($dbConfigUpdate==1) echo "<li> Konfiguration der Datenbank wird aktualisiert";
    
    if (!file_exists("user/cronHourly.php")) file_put_contents("user/cronHourly.php","<?php\n//Cronjob, der einmal pro Stunde aufgefunden wird\n\n?>");
    
    echo " <br><br>Installation erfolgreich!";
    exit ();
  } else if ($action == "updateFirmware")
  {
    ob_end_flush ();
    ob_start ();
    
    $firmwareIdFunctionId = getObjectFunctionsIdByName ( $BROADCAST_OBJECT_ID, "ModuleId" );
    $erg = QUERY ( "select id from featureFunctionParams where featureFunctionId='$firmwareIdFunctionId' and name='firmwareId' limit 1" );
    if ($row = MYSQLi_FETCH_ROW ( $erg ))
    {
      $paramId = $row [0];
      $erg = QUERY ( "select name from featureFunctionEnums where paramId='$paramId' and value='$firmwareId' limit 1" );
      if ($row = MYSQLi_FETCH_ROW ( $erg )) $firmwareName = $row [0];
      else die ( "Fehler paramId $paramId und firmwareId $firmwareId nicht gefunden" );
    } else die ( "Fehler featureFunctionId $firmwareIdFunctionId nicht gefunden" );
    
    if ($confirm == 1)
    {
      if ($actUpdateId == "")
      {
        $actUpdateId = 0;
        if (file_exists ( "../firmware/homeserver.sql" ))
          recoverDb ();
      }
      
      $erg = QUERY ( "select objectId,id,majorRelease, minorRelease,firmwareId,name from controller where id>'$actUpdateId' and size != '999' and online='1' and firmwareId='$firmwareId' order by id limit 1" );
      if ($obj = MYSQLi_FETCH_OBJECT ( $erg ))
      {
        $objectId = $obj->objectId;
        $actUpdateId = $obj->id;
        
        setupTreeAndContent ( "fwUpdate.html" );
        show ( 0 );
        
        if ($force != 1 && $obj->firmwareId == 1)
        {
          $onlineVersion = $_SESSION ["onlineVersionMainController"];
          $parts = explode ( ".", $onlineVersion );
          $major = $parts [0];
          $minor = $parts [1];
          if ($obj->majorRelease == $major && $obj->minorRelease == $minor)
          {
            liveOut ( "Controller: ObjectID: " . getFormatedObjectId ( $objectId ) . " hat bereits die neuste FW geladen ($obj->name)" );
            flushIt ();
            sleep ( 1 );
            die ( "<script>location='updatesProceed.php?action=$action&proceed=1&confirm=1&local=$local&firmwareId=$firmwareId&force=$force&actUpdateId=$actUpdateId';</script>" );
          }
        } else if ($force != 1 && $obj->firmwareId == 2)
        {
          $onlineVersion = $_SESSION ["onlineVersionMultiTaster"];
          $parts = explode ( ".", $onlineVersion );
          $major = $parts [0];
          $minor = $parts [1];
          if ($obj->majorRelease == $major && $obj->minorRelease == $minor)
          {
            liveOut ( "Controller: ObjectID: " . getFormatedObjectId ( $objectId ) . " hat bereits die neuste FW geladen ($obj->name)" );
            flushIt ();
            sleep ( 1 );
            die ( "<script>location='updatesProceed.php?action=$action&proceed=1&confirm=1&local=$local&firmwareId=$firmwareId&force=$force&actUpdateId=$actUpdateId';</script>" );
          }
        } else if ($force != 1 && $obj->firmwareId == 3)
        {
          $onlineVersion = $_SESSION ["onlineVersionMultiTasterSD6"];
          $parts = explode ( ".", $onlineVersion );
          $major = $parts [0];
          $minor = $parts [1];
          if ($obj->majorRelease == $major && $obj->minorRelease == $minor)
          {
            liveOut ( "Controller: ObjectID: " . getFormatedObjectId ( $objectId ) . " hat bereits die neuste FW geladen ($obj->name)" );
            flushIt ();
            sleep ( 1 );
            die ( "<script>location='updatesProceed.php?action=$action&proceed=1&confirm=1&local=$local&firmwareId=$firmwareId&force=$force&actUpdateId=$actUpdateId';</script>" );
          }
        }
        
        liveOut ( "<b>$obj->name </b><br>Firmwareupdate von Controller " . getFormatedObjectId ( $objectId ) );
        liveOut ( '' );
        flushIt ();
        
        if (getInstanceId ( $objectId ) != $BOOTLOADER_INSTANCE_ID)
        {
          liveOut ( "<b>Bootloader wird aktiviert ...</b>" );
          flushIt ();
          callObjectMethodByName ( $objectId, "reset" );
          $receiverObjectid = getObjectId ( getDeviceId ( $objectId ), getClassId ( $objectId ), $BOOTLOADER_INSTANCE_ID );
          
          sleepMs ( 1000 );
          $result = callObjectMethodByNameAndRecover ( $receiverObjectid, "ping", "", "pong", 1, 2, 0,"senderData" );
          //callObjectMethodByName ( $receiverObjectid, "ping" );
          //$result = waitForObjectResultByName ( $receiverObjectid, 5, "pong", $lastLogId, "senderData" );
          $objectId = $result->objectId;
          liveOut ( "Bootloader gestartet. ObjectID: " . getFormatedObjectId ( $objectId ) );
          liveOut ( '' );
        }
        
        liveOut ( "<b>Firmware Update ...</b>" );
        liveOut ( "Während des Updates den Controller und den PC NICHT AUSSCHALTEN!" );
        liveOut ( '' );
        flushIt ();
        
        $fwfile = "../firmware/" . $firmwareName . ".bin";
        if ($local == 1)
          $fwfile = "../firmware/" . $_SESSION ["actUpdateFile"];
        
        if (strpos ( $fwfile, "BOOTER" ) !== FALSE)
          $isBooter = 1;
        else
          $isBooter = 0;
        
        $fileSize = filesize ( $fwfile );
        liveOut ( "Datei: " . substr ( $fwfile, strrpos ( $fwfile, "/" ) + 1 ) );
        liveOut ( "Größe: $fileSize Bytes" );
        
        callObjectMethodByName ( $objectId, "getConfiguration" );
        
        $result = waitForObjectResultByName ( $objectId, 5, "Configuration", $lastLogId );
        $blockSize = getResultDataValueByName ( "dataBlockSize", $result );
        
        liveOut ( "Blockgröße: " . $blockSize . " Bytes" );
        liveOut ( '' );
        liveOut ( "<div id=\"status\">Updatestatus: 0/$fileSize Bytes - 0%</div>" );
        
        $fd = fopen ( $fwfile, "r" );
        $ready = 0;
        $round = 0;
        $firstWriteId = - 1;
        while ( ! feof ( $fd ) )
        {
          $buffer = fread ( $fd, $blockSize );
          $data ["address"] = $ready;
          $data ["data"] = $buffer;
          if ($firstWriteId == - 1)
            $firstWriteId = $lastLogId;
          
          $result = callObjectMethodByNameAndRecover ( $objectId, "writeMemory", $data, "MemoryStatus", 3, 2, 0 );
          if ($result == - 1)
          {
          	updateControllerStatus ();
            die ( "Fehler: Controller antwortet nicht" );
          }
          
          if ($result [0]->dataValue != 0)
          {
          	updateControllerStatus ();
            liveOut ( "Bootloader hat fehlerhaften MemoryStatus gemeldet: " . $result [0]->dataValue );
            exit ();
          }
          $ready += strlen ( $buffer );
          if ($round % 5 == 0 || ($fileSize - $ready < 1500)) statusOut ( $ready, $fileSize, $blockSize );
          
          $round ++;
          $i ++;
          flushIt ();
          
          sleepMS(5); //TODO warum hilft das ?
        }
        fclose ( $fd );
        
        liveOut ( "Übertragung erfolgreich beendet" );
        liveOut ( '' );
        
        if ($verify == 1)
        {
          liveOut ( "<b>Firmware wird verifiziert...</b>" );
          $erg = QUERY ( "select functionData,receiverSubscriberData from udpCommandLog where function='writeMemory' and id>'$firstWriteId' order by id" );
          while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
          {
            if (unserialize ( $row [1] )->objectId != $objectId)
              continue;
            
            $fkt = unserialize ( $row [0] );
            $offset = $fkp->paramData [0]->dataValue;
            $crc = $fkp->paramData [1]->dataValue;
            
            callObjectMethodByName ( $objectId, "readMemory", array (
                "address" => $offset,
                length => $blockSize 
            ) );
            $result = waitForObjectResultByName ( $objectId, 5, "MemoryData", $lastLogId );
            $compareCrc = getResultDataValueByName ( "data", $result );
            if ($compareCrc != $crc)
            {
              liveOut ( "Fehler bei offset: $offset -> " . $compareCrc . " != " . $crc );
              exit ();
            }
          }
          liveOut ( 'OK!' );
          liveOut ( '' );
        }

        liveOut ( "<b>Starte Controller neu...</b>" );
        callObjectMethodByName ( $objectId, "reset" );
        flush ();
        
        sleep ( 4 );
        
        for($i = 0; $i < 10; $i ++)
        {
          if ($isBooter != 1) $receiverObjectId = getObjectId ( getDeviceId ( $objectId ), getClassId ( $objectId ), $FIRMWARE_INSTANCE_ID );
          else $receiverObjectId = getObjectId ( getDeviceId ( $objectId ), getClassId ( $objectId ), $BOOTLOADER_INSTANCE_ID );
          callObjectMethodByName ( $receiverObjectId, "ping" );
          $result = waitForObjectResultByName ( $receiverObjectId, 5, "pong", $lastLogId, "funtionDataParams", 0 );
          if ($result != - 1)
            break;
          sleep ( 1 );
          if ($i == 9)
          {
            updateControllerStatus();
            liveOut ( "Fehler! Controller antwortet nicht" );
            exit ();
          }
        }

        // Wenn gerade die normale Firmware geladen wurde, nehmen wir anschließend den Booter offline, damit der nicht nochmal geladen wird
        if ($isBooter != 1)
        {
           $receiverObjectId = getObjectId ( getDeviceId ( $objectId ), getClassId ( $objectId ), $BOOTLOADER_INSTANCE_ID );
           QUERY("update controller set online='0' where objectId='$receiverObjectId' limit 1");
        }
        
        liveOut ( "Firmwareupdate erfolgreich beendet." );
        
        //updateControllerStatus (1);
        // sleep(3);
        flushIt ();
        die ( "<script>location='updatesProceed.php?proceed=1&action=$action&firmwareId=$firmwareId&local=$local&force=$force&confirm=1&actUpdateId=$actUpdateId';</script>" );
      }
      updateControllerStatus ();
      showMessage ( "Firmwareupdate erfolgreich beendet." );
    }
        
    
    if ($local != 1)
    {
      echo "<li> Firmware wird runtergeladen... <br>";
      flush ();
      ob_flush ();
      @mkdir ( "../firmware" );
      
      $result = download ( "http://www.haus-bus.de/" . $firmwareName . ".bin", "../firmware/" . $firmwareName . ".bin" );
      if (! $result)
        echo "Download Teil 1 fehlgeschlagen <br>";
      $result = download ( "http://www.haus-bus.de/" . $firmwareName . ".chk", "../firmware/" . $firmwareName . ".chk" );
      if (! $result)
        echo "Download Teil 2 fehlgeschlagen <br>";
      
      if ($firmwareName == "AR8")
        download ( "http://www.haus-bus.de/homeserver.sql", "../firmware/homeserver.sql" );
    }
    
    echo "<br>";
    die ( "<a href='updatesProceed.php?proceed=1&confirm=1&local=$local&action=$action&firmwareId=$firmwareId&force=$force' target='main'>Hier klicken, um Firmware nun zu laden</a>" );
  } else if ($action == "updateBooter")
  {
    $firmwareIdFunctionId = getObjectFunctionsIdByName ( $BROADCAST_OBJECT_ID, "ModuleId" );
    $erg = QUERY ( "select id from featureFunctionParams where featureFunctionId='$firmwareIdFunctionId' and name='firmwareId' limit 1" );
    if ($row = MYSQLi_FETCH_ROW ( $erg ))
    {
      $paramId = $row [0];
      $erg = QUERY ( "select name from featureFunctionEnums where paramId='$paramId' and value='$firmwareId' limit 1" );
      if ($row = MYSQLi_FETCH_ROW ( $erg )) $firmwareName = $row [0] . "_BOOTER";
      else die ( "Fehler paramId $paramId und firmwareId $firmwareId nicht gefunden" );
    } else die ( "Fehler featureFunctionId $firmwareIdFunctionId nicht gefunden" );
    
    if ($confirm == 1)
    {
      if ($actUpdateId == "") $actUpdateId = 0;
      
      $andSkip="and (1=2 ".$_SESSION["controllerWithEthernet"].")";

      if (isset($_SESSION["controllerWithEthernetSkipp"]))
      {
      	$andSkip = $_SESSION["controllerWithEthernetSkipp"];
        $erg = QUERY ( "select objectId,id,booterMajor, booterMinor,firmwareId,name from controller where id>'$actUpdateId' and size != '999' and online='1' and firmwareId='$firmwareId' $andSkip  order by id limit 1" );
        if ($obj = MYSQLi_FETCH_OBJECT ( $erg )){}
        else
        {
        	unset($_SESSION["controllerWithEthernetSkipp"]);
      	  $actUpdateId=0;
      	  $andSkip="and (1=2 ".$_SESSION["controllerWithEthernet"].")";
        }
      }

      $andDone="";
      if (isset($_SESSION["booterUpdateDone"])) $andDone="and not (1=2 ".$_SESSION["booterUpdateDone"].")";

      echo "select objectId,id,booterMajor, booterMinor,firmwareId,name from controller where id>'$actUpdateId' and size != '999' and online='1' and firmwareId='$firmwareId' $andSkip $andDone  order by id limit 1 <br>";
      
      $erg = QUERY ( "select objectId,id,booterMajor, booterMinor,firmwareId,name from controller where id>'$actUpdateId' and size != '999' and online='1' and firmwareId='$firmwareId' $andSkip $andDone  order by id limit 1" );
      if ($obj = MYSQLi_FETCH_OBJECT ( $erg ))
      {
        $objectId = $obj->objectId;
        $actUpdateId = $obj->id;
        $_SESSION["booterUpdateDone"].=" or id='$obj->id'";
        
        setupTreeAndContent ( "fwUpdate.html" );
        show ( 0 );
        
        if ($force != 1 && $obj->firmwareId == 1)
        {
          $onlineVersion = $_SESSION ["onlineVersionMainControllerBooter"];
          $parts = explode ( ".", $onlineVersion );
          $major = $parts [0];
          $minor = $parts [1];
          if ($obj->booterMajor == $major && $obj->booterMinor == $minor)
          {
            liveOut ( "Controller: ObjectID: " . getFormatedObjectId ( $objectId ) . " hat bereits die neuste FW geladen ($obj->name)" );
            flushIt ();
            sleep ( 1 );
            die ( "<script>location='updatesProceed.php?action=$action&proceed=1&confirm=1&local=$local&firmwareId=$firmwareId&force=$force&actUpdateId=$actUpdateId';</script>" );
          }
        } else if ($force != 1 && $obj->firmwareId == 2)
        {
          $onlineVersion = $_SESSION ["onlineVersionMultiTasterBooter"];
          $parts = explode ( ".", $onlineVersion );
          $major = $parts [0];
          $minor = $parts [1];
          if ($obj->booterMajor == $major && $obj->booterMinor == $minor)
          {
            liveOut ( "Controller: ObjectID: " . getFormatedObjectId ( $objectId ) . " hat bereits die neuste FW geladen ($obj->name)" );
            flushIt ();
            sleep ( 1 );
            die ( "<script>location='updatesProceed.php?action=$action&proceed=1&local=$local&confirm=1&firmwareId=$firmwareId&force=$force&actUpdateId=$actUpdateId';</script>" );
          }
        } else if ($force != 1 && $obj->firmwareId == 3)
        {
          $onlineVersion = $_SESSION ["onlineVersionMultiTasterBooterSD6"];
          $parts = explode ( ".", $onlineVersion );
          $major = $parts [0];
          $minor = $parts [1];
          if ($obj->booterMajor == $major && $obj->booterMinor == $minor)
          {
            liveOut ( "Controller: ObjectID: " . getFormatedObjectId ( $objectId ) . " hat bereits die neuste FW geladen ($obj->name)" );
            flushIt ();
            sleep ( 1 );
            die ( "<script>location='updatesProceed.php?action=$action&proceed=1&local=$local&confirm=1&firmwareId=$firmwareId&force=$force&actUpdateId=$actUpdateId';</script>" );
          }
        }
        
        liveOut ( "<b>$obj->name </b><br>Booterupdate von Controller " . getFormatedObjectId ( $objectId ) );
        liveOut ( '' );
        flushIt ();
        
        if (getInstanceId ( $objectId ) != $BOOTLOADER_INSTANCE_ID)
        {
          liveOut ( "<b>Bootloader wird aktiviert ...</b>" );
          flushIt ();
          callObjectMethodByName ( $objectId, "reset" );
          $receiverObjectid = getObjectId ( getDeviceId ( $objectId ), getClassId ( $objectId ), $BOOTLOADER_INSTANCE_ID );
          
          sleepMs ( 500 );
          
          $result = callObjectMethodByNameAndRecover ( $receiverObjectid, "ping", "", "pong", 3, 2, 0,"senderData" );

          //callObjectMethodByName ( $receiverObjectid, "ping" );
          //$result = waitForObjectResultByName ( $receiverObjectid, 5, "pong", $lastLogId, "senderData" );
          $objectId = $result->objectId;
          liveOut ( "Bootloader gestartet. ObjectID: " . getFormatedObjectId ( $objectId ) );
          liveOut ( '' );
        }
        
        liveOut ( "<b>Booter Update ...</b>" );
        liveOut ( "Während des Updates den Controller und den PC NICHT AUSSCHALTEN!" );
        liveOut ( '' );
        flushIt ();
        
        $fwfile = "../firmware/" . $firmwareName . ".bin";
        if ($local == 1)
          $fwfile = "../firmware/" . $_SESSION ["actUpdateFile"];
        
        $fileSize = filesize ( $fwfile );
        liveOut ( "Datei: " . substr ( $fwfile, strrpos ( $fwfile, "/" ) + 1 ) . " Größe: $fileSize Bytes" );
        
        callObjectMethodByName ( $objectId, "getConfiguration" );
        
        $result = waitForObjectResultByName ( $objectId, 5, "Configuration", $lastLogId );
        $blockSize = getResultDataValueByName ( "dataBlockSize", $result );
        
        liveOut ( "Daten Blockgröße: " . $blockSize . " Bytes" );
        liveOut ( '' );
        liveOut ( "<div id=\"status\">Updatestatus: 0/$fileSize Bytes - 0%</div>" );
        
		$memoryStatusOk = getFunctionParamEnumValueByName($objectId, "MemoryStatus", "status", "OK");
	    $memoryStatusAborted = getFunctionParamEnumValueByName($objectId, "MemoryStatus", "status", "ABORTED");
	
        $fd = fopen ( $fwfile, "r" );
        $ready = 0;
        $round = 0;
        $firstWriteId = - 1;
        while ( ! feof ( $fd ) )
        {
          $buffer = fread ( $fd, $blockSize );
          $data ["address"] = $ready;
          $data ["data"] = $buffer;
          if ($firstWriteId == - 1)
            $firstWriteId = $lastLogId;
          callObjectMethodByName ( $objectId, "writeMemory", $data );
          $result = waitForObjectResultByName ( $objectId, 2, "MemoryStatus", $lastLogId, "funtionDataParams", 0 );
          if ($result == - 1)
          {
            // Einmal wiederholen
            callObjectMethodByName ( $objectId, "writeMemory", $data );
            $result = waitForObjectResultByName ( $objectId, 2, "MemoryStatus", $lastLogId );
		  }
		  
		  $memoryStatus = getResultDataValueByName("status", $result);
		  
		  if ($memoryStatus != $memoryStatusOk)
          {
			if($memoryStatus == $memoryStatusAborted) 
			{
			  liveOut("Bootloader hat die FW nicht akzeptiert!  ");
			  liveOut("Mögliche Ursachen: ");
			  liveOut("- FW ist nicht für dieses Modul ");
			  liveOut("- FW Major-Release-Kennung ist vom Bootloader verschieden ");
			  liveOut("- FW Minor-Kennung ist nicht größer als bereits installiert ");
			  liveOut("- FW ist korrupt oder modifiziert ");
			}
			else
			{
			  liveOut("Bootloader hat fehlerhaften MemoryStatus gemeldet: " . $result[0]->dataValue);
			}
            exit ();
          }
          $ready += strlen ( $buffer );
          if ($round % 5 == 0 || ($fileSize - $ready < 1500))
            statusOut ( $ready, $fileSize, $blockSize );
          $round ++;
          $i ++;
          // sleepMS(50); //TODO warum hilft das ?
        }
        fclose ( $fd );
        
        liveOut ( "Übertragung erfolgreich beendet" );
        liveOut ( '' );
        
        if ($verify == 1)
        {
          liveOut ( "<b>Booter wird verifiziert...</b>" );
          $erg = QUERY ( "select functionData,receiverSubscriberData from udpCommandLog where function='writeMemory' and id>'$firstWriteId' order by id" );
          while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
          {
            if (unserialize ( $row [1] )->objectId != $objectId)
              continue;
            
            $fkt = unserialize ( $row [0] );
            $offset = $fkp->paramData [0]->dataValue;
            $crc = $fkp->paramData [1]->dataValue;
            
            callObjectMethodByName ( $objectId, "readMemory", array (
                "address" => $offset,
                length => $blockSize 
            ) );
            $result = waitForObjectResultByName ( $objectId, 5, "MemoryData", $lastLogId );
            $compareCrc = getResultDataValueByName ( "data", $result );
            if ($compareCrc != $crc)
            {
              liveOut ( "Fehler bei offset: $offset -> " . $compareCrc . " != " . $crc );
              exit ();
            }
          }
          liveOut ( 'OK!' );
          liveOut ( '' );
        }
        
        liveOut ( "<b>Starte Controller neu...</b>" );
        callObjectMethodByName ( $objectId, "reset" );
        flush ();
        
        sleep ( 4 );
        
        for($i = 0; $i < 10; $i ++)
        {
          $receiverObjectId = getObjectId ( getDeviceId ( $objectId ), getClassId ( $objectId ), $BOOTLOADER_INSTANCE_ID );
          callObjectMethodByName ( $receiverObjectId, "ping" );
          $result = waitForObjectResultByName ( $receiverObjectId, 5, "pong", $lastLogId, "funtionDataParams", 0 );
          if ($result != - 1)
            break;
          sleep ( 1 );
          if ($i == 9)
          {
            // updateControllerStatus();
            liveOut ( "Fehler! Controller antwortet nicht" );
            exit ();
          }
        }
        
        // Anschließend nehmen wir die normale Firmware offline, damit die nicht nochmal geladen wird
        $receiverObjectId = getObjectId ( getDeviceId ( $objectId ), getClassId ( $objectId ), $FIRMWARE_INSTANCE_ID);
        QUERY("update controller set online='0' where objectId='$receiverObjectId' limit 1");
        
        liveOut ( "Booterupdate erfolgreich beendet." );
        //updateControllerStatus (1);
        // sleep(3);
        flushIt ();
        die ( "<script>location='updatesProceed.php?proceed=1&action=$action&local=$local&firmwareId=$firmwareId&force=$force&confirm=1&actUpdateId=$actUpdateId';</script>" );
      }
      updateControllerStatus ();
      showMessage ( "Booterupdate erfolgreich beendet." );
    }
    
    if ($local != 1)
    {
      echo "<li> Booter wird runtergeladen... <br>";
      flush ();
      ob_flush ();
      @mkdir ( "../firmware" );
      
      $result = download ( "http://www.haus-bus.de/" . $firmwareName . ".bin", "../firmware/" . $firmwareName . ".bin" );
      if (! $result)
        echo "Download Teil 1 fehlgeschlagen <br>";
      $result = download ( "http://www.haus-bus.de/" . $firmwareName . ".chk", "../firmware/" . $firmwareName . ".chk" );
      if (! $result)
        echo "Download Teil 2 fehlgeschlagen <br>";
    }

    unset($_SESSION["booterUpdateDone"]);

    $andSkipped="";    
    $andAfterSkip="";
   	$erg = QUERY("select controller.id from controller left join featureInstances on (featureInstances.controllerId=controller.id) where featureClassesId=21");
  	while($obj=MYSQLi_FETCH_OBJECT($erg))
  	{
  		 $andSkipped.="and controller.id!='$obj->id' ";
  		 $andAfterSkip.="or controller.id='$obj->id' ";
  	}
    
    if ($andSkipped=="") unset($_SESSION["controllerWithEthernetSkipp"]);
    else $_SESSION["controllerWithEthernetSkipp"]=$andSkipped;
    
    if ($andAfterSkip=="") unset($_SESSION["controllerWithEthernet"]);
    else $_SESSION["controllerWithEthernet"]=$andAfterSkip;
    
    if ($firmwareId==1) // Wenn wir den Booter vom Hauptcontroller aktualisieren, sollte es keine Taster mit <= Version geben, weil die sonst anschließend ggf. nicht mehr erreichbar sind
    {
    	 $warning="";
    	 $biggestMainBooterMajor="";
    	 $biggestMainBooterMinor="";
    	 $biggestMainBooterModul="";
       $erg = QUERY("select name,firmwareId,majorRelease,minorRelease,booterMajor,booterMinor,online from controller where not (bootloader=1 and online!=1) and firmwareId='1'");
       while($obj=MYSQLi_FETCH_OBJECT($erg))
       {
       	  if ($biggestMainBooterMajor=="" || $obj->booterMajor>$biggestMainBooterMajor || ($obj->booterMajor==$biggestMainBooterMajor && $obj->booterMinor>$biggestMainBooterMinor))
       	  {
       	  	$biggestMainBooterMajor=$obj->booterMajor;
       	  	$biggestMainBooterMinor=$obj->booterMinor;
       	  	$biggestMainBooterModul=$obj->name;
       	  }
       }
       
       if ($biggestMainBooterMajor!="")
       {
         $erg = QUERY("select name,firmwareId,majorRelease,minorRelease,booterMajor,booterMinor,online from controller where not (bootloader=1 and online!=1) and firmwareId!=1 and firmwareId!=5");
         while($obj=MYSQLi_FETCH_OBJECT($erg))
         {
       	   if ($obj->booterMajor<$biggestMainBooterMajor || ($obj->booterMajor==$biggestMainBooterMajor && $obj->booterMinor<=$biggestMainBooterMinor))
       	   {
       	   	 $warning.="<li>".$obj->name."<br>";
       	   }
         }
       }
       
       if ($warning!="") echo "<br><font color=#bb0000><b>Achtung: Wenn der Booter der Maincontroller aktualisiert wird, sind anschließend IC2 Module ggf. nicht mehr erreichbar, falls diese nicht zuvor aktualisiert wurden!<br>Aktuell haben folgende Module eine potentiell zu kleine Booterversion und sollten zuerst aktualisiert werden:<br><font size=2>$warning </font></b><br><br>";
    }

    
    echo "<br>";
    die ( "<a href='updatesProceed.php?proceed=1&confirm=1&action=$action&firmwareId=$firmwareId&force=$force&local=$local' target='main'>Hier klicken, um Booter nun zu laden</a>" );
  }
  else if ($action == "updateSonoff")
  {
    ob_end_flush ();
    ob_start ();
    
    $firmwareName="sonoff";
    
    if ($confirm == 1)
    {
      if ($actUpdateId == "") $actUpdateId = 0;
      
      $erg = QUERY ( "select objectId,id,majorRelease, minorRelease,firmwareId,name from controller where id>'$actUpdateId'  and online='1' and firmwareId='$firmwareId' order by id limit 1" );
      if ($obj = MYSQLi_FETCH_OBJECT ( $erg ))
      {
        $objectId = $obj->objectId;
        $actUpdateId = $obj->id;
        
        setupTreeAndContent ( "fwUpdate.html" );
        show ( 0 );
        
        if ($force != 1)
        {
          $onlineVersion = $_SESSION ["onlineVersionSonoff"];
          $parts = explode ( ".", $onlineVersion );
          $major = $parts [0];
          $minor = $parts [1];
          if ($obj->majorRelease == $major && $obj->minorRelease == $minor)
          {
            liveOut ( "Controller: ObjectID: " . getFormatedObjectId ( $objectId ) . " hat bereits die neuste FW geladen ($obj->name)" );
            flushIt ();
            sleep ( 1 );
            die ( "<script>location='updatesProceed.php?action=$action&proceed=1&confirm=1&local=$local&firmwareId=$firmwareId&force=$force&actUpdateId=$actUpdateId';</script>" );
          }
        } 
        
        liveOut ( "<b>$obj->name </b><br>Firmwareupdate von Controller " . getFormatedObjectId ( $objectId ) );
        liveOut ( '' );
        flushIt ();
        
        liveOut ( "<b>Firmware Update ...</b>" );
        liveOut ( "Während des Updates den Controller und den PC NICHT AUSSCHALTEN!" );
        liveOut ( '' );
        flushIt ();
        
        $fwfile = "../firmware/".$firmwareName.".bin";
        if ($local == 1) $fwfile = "../firmware/" . $_SESSION ["actUpdateFile"];
        
        $fileSize = filesize ( $fwfile );
        liveOut ( "Datei: " . substr ( $fwfile, strrpos ( $fwfile, "/" ) + 1 ) );
        liveOut ( "Größe: $fileSize Bytes" );
        
        $currentReaderId = getObjectId(getDeviceId($objectId), 90, 1);
        callObjectMethodByName ( $currentReaderId, "getCurrentIp" );
        
        $result = waitForObjectResultByName ( $currentReaderId, 5, "CurrentIp", $lastLogId );
        $ip = getResultDataValueByName ( "IP0", $result ).".".getResultDataValueByName ( "IP1", $result ).".".getResultDataValueByName ( "IP2", $result ).".".getResultDataValueByName ( "IP3", $result );

        $serverIp = $_SERVER['SERVER_ADDR'];
        liveOut ( "IP vom Modul: $ip");
        liveOut ( "IP vom Server: $serverIp");
        
        $updateUrl = "http://".$ip."/update?ip=".$serverIp."&file=firmware/".$firmwareName.".bin";
        $result = file_get_contents($updateUrl);
        
        liveOut ( "Antwort vom Modul: ".$result );
        liveOut ( '' );
        
        liveOut ( "<b>Warte auf Neustart des Moduls...</b>" );
        flush ();
        sleep ( 4 );
        
        for($i = 0; $i < 10; $i ++)
        {
          callObjectMethodByName ( $objectId, "ping" );
          $result = waitForObjectResultByName ( $objectId, 5, "pong", $lastLogId, "funtionDataParams", 0 );
          if ($result != - 1)
            break;
          sleep ( 1 );
          if ($i == 9)
          {
            updateControllerStatus();
            liveOut ( "Fehler! Controller antwortet nicht" );
            exit ();
          }
        }
        
        liveOut ( "Firmwareupdate erfolgreich beendet." );
        
        //updateControllerStatus (1);
        // sleep(3);
        flushIt ();
        die ( "<script>location='updatesProceed.php?proceed=1&action=$action&firmwareId=$firmwareId&local=$local&force=$force&confirm=1&actUpdateId=$actUpdateId';</script>" );
      }
      updateControllerStatus ();
      showMessage ( "Firmwareupdate erfolgreich beendet." );
    }
    
    if ($local != 1)
    {
    	$firmwareName = strtolower($firmwareName);
    	
      echo "<li> Firmware wird runtergeladen... <br>";
      flush ();
      ob_flush ();
      @mkdir ( "../firmware" );
      
      $result = download ( "http://www.haus-bus.de/" . $firmwareName . ".bin", "../firmware/" . $firmwareName . ".bin" );
      if (! $result)
        echo "Download Teil 1 fehlgeschlagen <br>";
      $result = download ( "http://www.haus-bus.de/" . $firmwareName . ".chk", "../firmware/" . $firmwareName . ".chk" );
      if (! $result)
        echo "Download Teil 2 fehlgeschlagen <br>";
    }
    
    echo "<br>";
    die ( "<a href='updatesProceed.php?proceed=1&confirm=1&local=$local&action=$action&firmwareId=$firmwareId&force=$force' target='main'>Hier klicken, um Firmware nun zu laden</a>" );
   }
}
function statusOut($bytes, $fileSize, $blockSize)
{
  $percent = ( int ) ($bytes * 100 / $fileSize);
  echo "<script>document.getElementById(\"status\").innerHTML=\"Updatestatus: $bytes/$fileSize Bytes - $percent%\";</script>";
}

$orig = $_FILES ['userfile'] ['name'];
if ($orig != "")
{
  $local = 1;
  move_uploaded_file ( $_FILES ['userfile'] ['tmp_name'], "../firmware/" . $orig );
  $_SESSION ["actUpdateFile"] = $orig;
}

setupTreeAndContent ( "updatesProceed_design.html", $message );

$html = str_replace ( "%ACTION%", $action, $html );
$html = str_replace ( "%FORCE%", $force, $html );
$html = str_replace ( "%FIRMWARE_ID%", $firmwareId, $html );
$html = str_replace ( "%LOCAL%", $local, $html );

show ();
function unzip($src)
{
  $zip = new ZipArchive ();
  if ($zip->open ( $src ) === TRUE)
  {
    for($i = 0; $i < $zip->numFiles; $i ++)
    {
      $actFileName = $zip->statIndex ( $i )["name"];
      if ((strpos ( $actFileName, ".exe" ) !== FALSE || strpos ( $actFileName, ".cmd" ) !== FALSE) && file_exists ( str_replace ( "homeserver/", "", $actFileName ) ))
        $zip->deleteName ( $actFileName );
    }
    
    $result = $zip->extractTo ( '../' );
    $zip->close ();
    return $result;
  } else
    return FALSE;
}
function download($src, $dest)
{
  return @file_put_contents($dest, @file_get_contents ( $src, False, getStreamContext () ) );
}
function backup($srcDir, $destZip)
{
  $zip = new Zipper ();
  if ($zip->open ( $destZip, ZIPARCHIVE::OVERWRITE ) === TRUE)
  {
    $zip->addDir ( $srcDir );
    $zip->close ();
    return TRUE;
  } else
    return FALSE;
}
function recurse_copy($src, $dst)
{
  $dir = opendir ( $src );
  @mkdir ( $dst );
  while ( false !== ($file = readdir ( $dir )) )
  {
    if (($file != '.') && ($file != '..'))
    {
      if (is_dir ( $src . '/' . $file ))
      {
        $result = recurse_copy ( $src . '/' . $file, $dst . '/' . $file );
        if (! result)
          return FALSE;
      } else
      {
        $result = copy ( $src . '/' . $file, $dst . '/' . $file );
        if (! $result)
          return FALSE;
      }
    }
  }
  closedir ( $dir );
  return TRUE;
}
class Zipper extends ZipArchive
{
  public function addDir($path)
  {
    $this->addEmptyDir ( $path );
    $nodes = glob ( $path . '/*' );
    foreach ( $nodes as $node )
    {
      if (is_dir ( $node ))
      {
        $this->addDir ( $node );
      } else if (is_file ( $node ))
      {
        if (strpos ( $node, ".exe" ) === FALSE)
          $this->addFile ( $node );
      }
    }
  }
}

function dbUpdate($dbUpdate, $table)
{
  $pos = strpos ( $dbUpdate, "INSERT INTO `$table`" );
  if ($pos===FALSE) liveOut ( "Fehler! Eintrag für Tabelle $table nicht gefunden" );
  else
  {
    $sql = "TRUNCATE table $table";
    //echo $sql."<br>";
    QUERY ( $sql );

    $errorCount=0;
    while($pos !== FALSE && $errorCounter<50)
    {
    	$errorCounter++;
    	
      $pos2 = strpos ( $dbUpdate, ";", $pos );
      $sql = trim ( substr ( $dbUpdate, $pos, $pos2 - $pos ) );
      //echo $sql."<br>";
      QUERY ( $sql );
      
      $pos = strpos ( $dbUpdate, "INSERT INTO `$table`",$pos+10);
    }
  }
}

function recoverDb()
{
  liveOut ( "Aktualisiere Datenbank...." );
  flushIt ();
  $dbUpdate = file_get_contents ( "../firmware/homeserver.sql" );
  $dbUpdate = utf8_decode($dbUpdate);
  
  dbUpdate ( $dbUpdate, "featureclasses" );
  dbUpdate ( $dbUpdate, "featurefunctionbitmasks" );
  dbUpdate ( $dbUpdate, "featurefunctionenums" );
  dbUpdate ( $dbUpdate, "featurefunctionparams" );
  dbUpdate ( $dbUpdate, "featurefunctions" );
}

function updateDatabase()
{
	for ($i=0;$i<50;$i++)
	{
		$file = "dbUpdate".$i.".php";
		$fileDone = "dbUpdate".$i.".php.1";
		$nextFile = "dbUpdate".($i+1).".php";
		
		if ($i==0)
		{
			$file = "dbUpdate.php";
			$fileDone = "dbUpdate.php.1";
		}
		
		//echo $file." // ".$fileDone." // ".$nextFile."<br>";
		
		if (file_exists($file))
		{
			if (!file_exists($fileDone) || !file_exists($nextFile))
			{
			  echo "<li> Bearbeite Datenbankupdate $i";
        flushIt ();
        include $file;
        file_put_contents($file.".1","ok");
      }
		}
		else return;
	}
}
?>



