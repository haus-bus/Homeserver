<?php

print_r(getWeather());

function getWeather() 
{
	$lat= "51.7177"; 
	$lng = "8.7527";
	$apiKey = "2f88e986e72c9e592cd6698340c0aabd";
  $data = new stdClass();

  try 
  {
    $url = 'http://api.openweathermap.org/data/2.5/weather?lat='.$lat.'&lon='.$lng.'&APPID='.$apiKey;
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
    $data = curl_exec($ch);

    $data = json_decode(utf8_encode($data));

    if($data->cod != "200") $data->error = "Failed retrieving weather data.";
    else 
    {
        $res = $data->current_condition;
        $main = $data->main;
        $wind = $data->wind;
        $data->tempC = round(($main->temp -273.15), 0);
        $data->tempF = round(((($data->tempC * 9 ) / 5) + 32) ,0);
        $data->pressure = $main->pressure;
        $data->ws_miles = $wind->speed;
        $data->ws_kts = round($data->ws_miles * 0.868976242, 0);
        $data->winddirDegree = round($wind->deg);
        $data->visibility = $res->visibility; // In Kilometer
    }
  } 
  catch(Exception $ex) 
  {
    $data->error = $ex->getMessage();
  }
  
  return $data;
}

exit;

include("include/all.php");
$MAX_LOG_ENTRIES=100000;
cleanUp();

die("5:test");

die("A".getSonoffWatt("192.168.178.117")."A");

function _detectFileEncoding($filepath) 
{
    // VALIDATE $filepath !!!
    $output = array();
    exec('file -i ' . $filepath, $output);
    if (isset($output[0]))
    {
        $ex = explode('charset=', $output[0]);
        return isset($ex[1]) ? $ex[1] : null;
    }
    return null;
}

function getSonoffWatt($ip)
{
  $erg = file_get_contents("http://$ip/ay");
  $pos = strpos($erg,"Power");
  $pos = strpos($erg,"}",$pos);
  $pos2 = strpos($erg," W",$pos);
  $watt = substr($erg,$pos+1,$pos2-$pos-1);
  return $watt;
}



for ($u=0;$u<20;$u++)
{
  if ($u%2==0) $val=100;
  else $val=0; 
  for ($i=1;$i<7;$i++)
  {
  	sendLoxone("1620000171.LED.$i=$val.");//.($u*20+$i));
  	usleep(20000);
  }
}



function sendLoxone($binary_msg)
{
  $datagrammPos=strlen($binary_msg);
  $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
  if ($s == false)
  {
    echo "Error creating socket!\n";
    echo "Error code is '".socket_last_error($s)."' - " . socket_strerror(socket_last_error($s));
  }
  else
  {
    // setting a broadcast option to socket:
    $opt_ret = socket_set_option($s, 1, 6, TRUE);
    if($opt_ret < 0) echo "setsockopt() failed, error: " . strerror($opt_ret) . "\n";
    $e = socket_sendto($s, $binary_msg, $datagrammPos, 0, "192.168.178.255", 15557);
    socket_close($s);
  }
}

exit;
      
logMe("Opening UDP Socket on port $UDP_PORT"); 
if (($udpSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false) die("UDP socket_create() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error()));
if (!socket_set_option($udpSocket, SOL_SOCKET, SO_REUSEADDR, 1)) die("UDP Could not set option SO_REUSEADDR to socket: ". socket_strerror(socket_last_error()) . PHP_EOL);
if (!socket_set_option($udpSocket, 1, 6, TRUE)) die("UDP Could not set broadcast option to socket: ". socket_strerror(socket_last_error()) . PHP_EOL);
if (!socket_set_nonblock($udpSocket)) die("UDP Could not set socket to non blocking mode: ". socket_strerror(socket_last_error()) . PHP_EOL);
if (socket_bind($udpSocket, 0, $UDP_PORT) === false) traceError("UDP socket_bind() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)));
logMe( "UDP server ready ");
if ($udpOutput==0) logMe( "in SILENT MODE ! \n");
else logMe( "in VERBOSE MODE ! \n");


error_reporting (E_ALL);
include("include/all.php");
print_r(parseWeekTime(54820));
die("ende");
exit;

/* Das Skript wartet auf hereinkommende Verbindungsanforderungen. */
set_time_limit (0);

/* Die implizite Ausgabe wird eingeschaltet, so dass man sieht,
 * was gesendet wurde. */
ob_implicit_flush ();

$port = 9500;

$ifConfig = shell_exec('/sbin/ifconfig eth0');
$pos = strpos($ifConfig,"inet Adr");
$pos = strpos($ifConfig,":",$pos);
$pos2 = strpos($ifConfig," ",$pos);
$address = substr($ifConfig,$pos+1,$pos2-$pos-1);

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "socket_create() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error()) . "\n";
}

if (socket_bind($sock, $address, $port) === false) {
    echo "socket_bind() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)) . "\n";
}

if (socket_listen($sock, 5) === false) {
    echo "socket_listen() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)) . "\n";
}

do {
    if (($msgsock = socket_accept($sock)) === false) {
        echo "socket_accept() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)) . "\n";
        break;
    }
    /* Anweisungen senden. */
    $msg = "\nWillkommen auf dem PHP-Testserver.  \n" .
        "Um zu beenden, geben Sie 'quit' ein. Um den Server herunterzufahren, geben Sie 'shutdown' ein.\n";
    socket_write($msgsock, $msg, strlen($msg));

    do {
        if (false === ($buf = socket_read ($msgsock, 2048))) {
            echo "socket_read() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($msgsock)) . "\n";
            break 2;
        }
        if (!$buf = trim ($buf)) {
            continue;
        }
        if ($buf == 'quit') {
            break;
        }
        if ($buf == 'shutdown') {
            socket_close ($msgsock);
            break 2;
        }
        $talkback = "welcome PHP: Sie haben '$buf' eingegeben.\n";
        socket_write ($msgsock, $talkback, strlen ($talkback));
        echo "$buf\n";
    } while (true);
    socket_close ($msgsock);
} while (true);

socket_close ($sock);

exit;
include("include/all.php");

die(getWoeID());


die("<html><body><form action='http://192.168.178.80/test' method=POST><input type=hidden name=test value='123456789'><input type=submit value='send'></form>");

$errorStrom=0;
$errorTisch=0;
$errorWemos=0;

for ($i=0;$i<10000;$i++)
{
	$result = executeCommand("6488065", "getModuleId", "", "ModuleId");
	if ($result==-1) $errorStrom++;
	$result = executeCommand("7274497", "getModuleId", "", "ModuleId");
	if ($result==-1) $errorTisch++;
	$result = executeCommand("7208961", "getModuleId", "", "ModuleId");
	if ($result==-1) $errorWemos++;
	
	echo $i.": Strom=$errorStrom Tisch=$errorTisch Wemos=$errorWemos <br>";
	
	sleep(0,5);
}



exit;
$min = time()-25*60*60;
$erg = QUERY("select time,functionData from udpcommandlog where senderObj='6511105' and time>'$min' order by id");
while($obj=mysqli_fetch_OBJECT($erg))
{
	 $data = unserialize($obj->functionData);
	 if ($data->name=="evSignal")
	 {
	 	  $power = $data->paramData[2]->dataValue;
	 	  $duration = $data->paramData[3]->dataValue;
	 	  //if ($duration<88 || $duration > 92)
	 	   echo date("d.m.Y H:i:s",$obj->time).": ".$power." - ".$duration."<br>";
	 }
}

exit;

foreach ($minData as $time=>$val)
{
	 $minData[$time]=round($minData[$time]/$minValues[$time]);
	 if ($graphData[1]!="") $graphData[1].=",";
	 $graphData[1].= "[".($time*1000).",".$minData[$time]."]";
}
$html = file_get_contents("templates/showGraph_design.html");

$html = str_replace("%TITLE%","title",$html);
$height=",height: window.innerHeight/100*90";
$html = str_replace("%height%",$height,$html);

$html = str_replace("%SUBTITLE%","",$html);
$html = str_replace("%X_AXIS_MIN_RANGE%","1",$html);
$html = str_replace("%Y_AXIS_TITLE%","",$html);
$html = str_replace("%THEME%","default",$html);

$seriesTag=getTag("%SERIES%",$html);
$series="";

$actTag = $seriesTag;
$actTag = str_replace("%SIGNAL_NAME%","minuten",$actTag); 
$actTag = str_replace("%SIGNAL_COLOR%","450DFF",$actTag);
$actTag = str_replace("%SERIES_TYPE%","",$actTag);
$actTag = str_replace("%STEPS%","",$actTag);
$actTag = str_replace("%data%",$graphData[1],$actTag);
$series.=$actTag;
$html = str_replace("%SERIES%",$series,$html);

die($html);
print_r($minData);
print_r($minValues);
die("ende");



$objectId="100990977";
$receiverObjectId = getObjectId(getDeviceId($objectId), getClassId($objectId), $BOOTLOADER_INSTANCE_ID);
$fwfile = "../firmware/AR8.bin";
$isBooter = 1;
        
$fileSize = filesize ( $fwfile );
$blockSize = 512;
$fd = fopen ( $fwfile, "r" );
$ready = 0;
$round = 0;
$firstWriteId = - 1;
while ( ! feof ( $fd ) )
{
  $buffer = fread ( $fd, $blockSize );
  $data ["address"] = $ready;
  $data ["data"] = $buffer;
  if ($firstWriteId == - 1) $firstWriteId = $lastLogId;
  callInstanceMethodForObjectId($objectId, 67,$data); // writeMemory
  $ready += strlen ( $buffer );
  $round ++;
  $i ++;
  sleepMS(100); //TODO warum hilft das ?
}
fclose ( $fd );
callInstanceMethodForObjectId($objectId, 38,$data); // reset
die("ende");
exit;
        


$objectId="100990977";
//callInstanceMethodForObjectId($objectId, 31,$commandData); // getModuleId
//callInstanceMethodForObjectId($objectId, 38,$commandData); // reset
//sleepMs(500);
callInstanceMethodForObjectId($objectId, 68,$commandData); // ping
echo "A".$debug."<br>";
exit;

$receiverObjectId = getObjectId(getDeviceId($objectId), getClassId($objectId), $BOOTLOADER_INSTANCE_ID);
for ($i=0;$i<5;$i++)
{
  sleepMs(100);
  callInstanceMethodForObjectId($receiverObjectId, 68);
echo "B".$debug."<br>";
}

exit;

$erg = QUERY("select id, udpDataLogId from udpcommandlog where senderObj='6511105'");
while($obj=mysqli_fetch_OBJECT($erg))
{
	QUERY("delete from udpdatalog where id='$obj->udpDataLogId' limit 1");
	QUERY("delete from udpcommandlog where id='$obj->id' limit 1");
	
}

exit;


print_r(parseWeekTime(212*256+47));
exit;

die("A".toWeekTime(date("N")-1,date("H"),date("i")));

      	$commandData["index"]="1"; 

$objectId="28442625";
//print_r(getFormatedObjectId($objectId));
//exit;
//callInstanceMethodForObjectId($objectId, 31,$commandData); // getModuleId
callInstanceMethodForObjectId($objectId, 38,$commandData); // reset
sleepMs(500);
callInstanceMethodForObjectId($objectId, 68,$commandData); // ping
echo "A".$debug."<br>";
exit;

$receiverObjectId = getObjectId(getDeviceId($objectId), getClassId($objectId), $BOOTLOADER_INSTANCE_ID);
for ($i=0;$i<5;$i++)
{
  sleepMs(100);
  callInstanceMethodForObjectId($receiverObjectId, 68);
echo "B".$debug."<br>";
}
      
die("ende");
      
//die("A".(date("N")-1)." - ".date("d"));
//die(toWeekTime(1, $hour, $minute));
print_r(parseWeekTime(46127));
exit;

//download ( "http://www.haus-bus.de/homeserver.sql", "../firmware/homeserver.sql" );
recoverDb();

function dbUpdate($dbUpdate, $table)
{
  $pos = strpos ( $dbUpdate, "INSERT INTO `$table`" );
  if ($pos===FALSE) liveOut ( "Fehler! Eintrag für Tabelle $table nicht gefunden" );
  else
  {
    $sql = "TRUNCATE table $table";
    echo $sql."<br>";
    //QUERY ( $sql );

    $errorCount=0;
    while($pos !== FALSE && $errorCounter<50)
    {
    	$errorCounter++;
    	
      $pos2 = strpos ( $dbUpdate, ";", $pos );
      $sql = trim ( substr ( $dbUpdate, $pos, $pos2 - $pos ) );
       echo $sql."<br>";
      //QUERY ( $sql );
      
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
  
  //dbUpdate ( $dbUpdate, "featureclasses" );
  //dbUpdate ( $dbUpdate, "featurefunctionbitmasks" );
  //dbUpdate ( $dbUpdate, "featurefunctionenums" );
  dbUpdate ( $dbUpdate, "featurefunctionparams" );
  //dbUpdate ( $dbUpdate, "featurefunctions" );
}



function download($src, $dest)
{
  return @file_put_contents($dest, @file_get_contents ( $src, False, getStreamContext () ) );
}

exit;

$erg = QUERY("select classesId from functiontemplates where name=''");
while($obj=mysqli_fetch_OBJECT($erg)) $existingTemplates[$obj->classesId]=1;

if ($existingTemplates[9]!=1) QUERY("INSERT INTO functiontemplates (`classesId`, `function`, `signal`, `name`) VALUES (9, 5, '', ''),(9, 4, 'doubleClick', ''),(9, 3, 'hold', ''),(9, 2, 'click', ''),(9, 1, 'click', '')");
if ($existingTemplates[14]!=1) QUERY("INSERT INTO functiontemplates (`classesId`, `function`, `signal`, `name`) VALUES (14, 5, 'doubleClick', ''),(14, 4, '-', ''),(14, 3, '-', ''),(14, 2, 'hold', ''),(14, 1, 'click', '')");
if ($existingTemplates[18]!=1) QUERY("INSERT INTO functiontemplates (`classesId`, `function`, `signal`, `name`) VALUES (18, 4, 'doubleClick', ''),(18, 3, '', ''),(18, 2, 'covered', ''),(18, 1, 'covered', ''),(18, 5, '', '')");
if ($existingTemplates[8]!=1) QUERY("INSERT INTO functiontemplates (`classesId`, `function`, `signal`, `name`) VALUES (8, 5, '', ''),(8, 4, 'hold', ''),(8, 3, '', ''),(8, 2, 'covered', ''),(8, 1, 'covered', '')");
if ($existingTemplates[17]!=1) QUERY("INSERT INTO functiontemplates (`classesId`, `function`, `signal`, `name`) VALUES (17, 1, 'covered', ''),(17, 2, 'covered', ''),(17, 3, '', ''),(17, 4, 'hold', ''),(17, 5, '', '')");
if ($existingTemplates[1]!=1) QUERY("INSERT INTO functiontemplates (`classesId`, `function`, `signal`, `name`) VALUES (1, 5, '', ''),(1, 4, '', ''),(1, 3, '', ''),(1, 2, '-', ''),(1, 1, '-', '')");

die("ende");
$schalterOnFunctionId = getFunctionsIdByNameForClassName("Schalter", "on");
$schalterOffFunctionId = getFunctionsIdByNameForClassName("Schalter", "off");
$schalterEvOnFunctionId = getFunctionsIdByNameForClassName("Schalter", "evOn");
$schalterEvOffunctionId = getFunctionsIdByNameForClassName("Schalter", "evOff");

die($schalterOnFunctionId."-".$schalterOffFunctionId."-".$schalterEvOnFunctionId."-".$schalterEvOffunctionId);

$erg = QUERY("select paramValue,paramKey from basicConfig where paramKey = 'locationZipCode' or paramKey='locationCountry' or paramKey='latitude' or paramKey='longitude'");
while($row=mysqli_fetch_ROW($erg))
{
	 $vals[$row[1]]=$row[0];
}

$sunset =  date_sunset ( time(), SUNFUNCS_RET_STRING, $vals['latitude'], $vals['longitude'],ini_get("date.sunset_zenith"),date("Z")/3600);

die($sunset);
?>