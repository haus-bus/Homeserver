<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($action == "install")
{
	@mkdir("libs");
	file_put_contents("libs/google.zip",file_get_contents("http://www.haus-bus.de/google-api-php-client-master.zip"));
	$erg = shell_exec("unzip libs/google.zip -d libs/");
	unlink("libs/google.zip");
}
else if($action=="add")
{
	 if ($calendarName=="") $error="Bitte Kalendernamen angeben";
	 else if ($calendarId=="")  $error="Bitte Kalender-ID angeben";
	 else
	 {
	 	  $calendarId=trim($calendarId);
	 	  QUERY("INSERT into googleCalendar (name, calendarId) values('$calendarName','$calendarId')"); 
	 	  $calendarName="";
	 	  $calendarId="";
	 	  $error="OK";
	 }
}
else if($action=="delete")
{
	QUERY("DELETE from googleCalendar where id='$id' limit 1"); 
  $error="Kalender wurde gelöscht";
}
else if($action=="test")
{
	 $erg = QUERY("select * from googleCalendar where id='$id' limit 1");
	 $obj=mysqli_fetch_OBJECT($erg);
	 echo "<b>Teste Kalender ".$obj->calendarId."</b><br><br><pre>";
	 
	 require_once 'libs/google-api-php-client-master/src/Google/autoload.php';

   $client_id = '835303662489-6f3hjltkn8ahoe82rajcf9o9grbdh332.apps.googleusercontent.com';
   $Email_address = '835303662489-6f3hjltkn8ahoe82rajcf9o9grbdh332@developer.gserviceaccount.com';	 
   $key_file_location = '/var/www/haus-bus-de homeserver-325876d8d669.p12';	 	
   $client = new Google_Client();	 	
   $client->setApplicationName("Haus-Bus-DE Homeserver");
   $key = file_get_contents($key_file_location);	 
   $scopes ="https://www.googleapis.com/auth/calendar.readonly"; 	
   $cred = new Google_Auth_AssertionCredentials($Email_address, array($scopes), $key	);	 	
   $client->setAssertionCredentials($cred);
   if($client->getAuth()->isAccessTokenExpired()) $client->getAuth()->refreshTokenWithAssertion($cred);	 	
   $service = new Google_Service_Calendar($client);    

   $params = array
   (
    //CAN'T USE TIME MIN WITHOUT SINGLEEVENTS TURNED ON,
    //IT SAYS TO TREAT RECURRING EVENTS AS SINGLE EVENTS
    'singleEvents' => true,
    'orderBy' => 'startTime',
    'timeMin' => date(DateTime::ATOM) //ONLY PULL EVENTS STARTING TODAY
   );

   //THIS IS WHERE WE ACTUALLY PUT THE RESULTS INTO A VAR
   $events = $service->events->listEvents($obj->calendarId, $params);
   echo "<pre>";
   foreach ($events->getItems() as $event) 
   {
   	  $ok=1;
    	print_r($event);
   }
   if ($ok!=1) echo "Keine Termine gefunden";
   echo "</pre><br>";
   exit;
}

setupTreeAndContent("editGoogleCalendar_design.html", $message);
if ($error!="") $error="<b>$error</b><br><br>"; 
$html = str_replace("%ERROR%",$error,$html);
$html = str_replace("%CALENDAR_NAME%",$calendarName,$html);
$html = str_replace("%CALENDAR_ID%",$calendarId,$html);

if (!file_exists("libs/google-api-php-client-master"))
{
	 chooseTag("%OPT_NOT_INSTALLED%",$html);
	 removeTag("%OPT_INSTALLED%",$html);
	 show();
}

if (!file_exists("../haus-bus-de homeserver-325876d8d669.p12")) showMessage("P12 Datei nicht gefunden. Bitte email an info@haus-bus.de");

removeTag("%OPT_NOT_INSTALLED%",$html);
chooseTag("%OPT_INSTALLED%",$html);

$calendarTag = getTag("%CALENDAR%",$html);
$elements="";
$erg = QUERY("select * from googleCalendar order by id");
while($obj=mysqli_fetch_OBJECT($erg))
{
	  $actTag=$calendarTag;
	  $actTag = str_replace("%ID%",$obj->id,$actTag);
	  $actTag = str_replace("%NAME%",$obj->name,$actTag);
	  $actTag = str_replace("%ACT_CALENDAR_ID%",$obj->calendarId,$actTag);
	  
	  $elements.=$actTag;
}

$html = str_replace("%CALENDAR%",$elements,$html);

show();

?>
