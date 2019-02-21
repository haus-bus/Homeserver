<?php
/*
Mit dieser Klasse lassen sich eigene Plugins für das Bussystem schreiben.
Man wird über alle eintreffenden Nachrichten informiert und kann beliebige Aktionen auslösen.
Zusätzlich gibt es Hilfsfunktionen, mit denen man sich Zustände merken kann.

ACHTUNG: Nicht diese Datei ändern, sondern die Datei myUserPlugin.php, da diese Datei mit jedem Update überschrieben wird !!

ACHTUNG: Jedes mal, wenn dieses Skript geändert wird, muss es einmal neu eingelesen werden.
Dies kann man erledigen, indem in der Homeserveroberfläche beim Raspberry -> Executor die Funktion reloadUserPlugin aufgerufen wird

*/

include_once("/var/www/homeserver/user/myUserPlugin.php"); 


/*
 Wird bei jedem eintreffenden Event aufgerufen.
 Achtung: Funktion läuft im Prozess, der die UDP Daten verarbeitet. Bitte keine Langlaufaktionen oder sleeps verwenden.
*/
function eventOccured($senderData, $receiverData, $functionData)
{
	/* 
	Beschreibung $senderData und $receiverData Objekt	
	Die dbIDs sind eigentlich nur intern und sollten nicht verwendet werden
	ACHTUNG: Wenn es sich bei dem Event um einen Broadcast handelt, ist in $receiverData instanceObjectId=0 und der Rest leer!
	stdClass Object
  (
    [instanceObjectId] => 1493181016          // ObjektId des Eventsenders bzw. Empfängers (Bei Broadcast ist der Empfänger 0)
    [instanceDbId] => 10275                   // DatenbankId des Eventsenders bzw. Empfängers (intern)
    [instanceName] => Feuchtesensor Küche     // Vom Benutzer vergebener Name des Eventsenders bzw. Empfängers
    [classId] => 34                           // ClassId der Kasse des Eventsenders bzw. Empfängers
    [classDbId] => 23                         // DatenbankId der Klasse des Eventsenders bzw. Empfängers (intern)
    [className] => Feuchtesensor              // Name der Klasse des Eventsenders bzw. Empfängers
    [controllerObjectId] => 1493172225        // ObjektId des versendenden bzw. empfangenden Controllers (Also der Controller auf dem die Sender- oder Empfängerinstanz läuft)
    [controllerDbId] => 3                     // DatenbankId des versendenden bzw. empfangenden Controllers (intern)
    [controllerName] => Herms Ecke            // Vom Benutzer vergebener Name des versendenden bzw. empfangenden Controllers
    [roomDbId] => 8                           // DatenbankId des ersten Raums, dem der Sender bzw. Empfängers oder leer, wenn keinem Raum zugeordnet
    [roomName] => Küche                       // Vom Benutzer vergebener Name des ersten Raums, dem der Sender bzw. Empfängers oder leer, wenn keinem Raum zugeordnet
 )
 
 Beschreibung $functionData
 stdClass Object
 ( 
    [functionId] => 129                       // Beim Eventtyp ACTION oder FUNCTION (siehe type) ist dies die FunktionsId der Funktion, die beim Empfänger aufgerufen wird
                                              // Beim Eventtyp RESULT oder EVENT    (siehe type) ist dies die FunktionsId des Events oder Ergebnisses wie vom Sender verschickt
    [functionDbId] => 187                     // DatenbankId der Funktion  (intern)
    [classId] => 34                           // ClassId der Klasse der Funktion
    [classDbId] => 187                        // DatenbankId Klasse der Funktion  (intern)
    [type] => EVENT                           // Nachrichtentyp ACTION,FUNCTION,RESULT,EVENT
    [name] => Status                          // Name der Funktion wie im Homeserver angezeigt
    [data] => Array                           // Ergebnisarray mit entsprechend vielen Elementen wie bei der jeweiligen Funktion definiert.
        (
            [0] => stdClass Object            // Element 0
                (
                    [id] => 369               // DatenbankId dieses Parameters (intern)
                    [name] => relativeHumidity// Name dieses Paramters
                    [type] => WORD            // Datentyp dieses Parameters 
                    [dataValue] => 58         // Wert dieses Parameter (Hier wurde also ein Event verschickt und als Luftfeuchtigkeit 58% gemeldet
                )

            [1] => stdClass Object            // Element 1
                (
                    [id] => 423               // DatenbankId dieses Parameters (intern)
                    [name] => lastEvent       // Name dieses Paramters
                    [type] => ENUM            // Datentyp dieses Parameters 
                    [dataName] => CONFORTABLE // Beim Datentyp ENUM zusätzliche Info zum Namen des Parameterwertes. Wert 201 entspricht also CONFORTABLE
                    [dataValue] => 201        // Wert dieses Parameter (Hier der Enumwert für CONFORTABLE)
                )
        )
 )
 */
 
  /* Einfaches Beispiel:
  // Wenn vom Absender mit der ObjectId 1313542167 (Siehe Homeserver dezimale ObjektId) ein Event eintrifft mit Namen "evClicked"
  if ($senderData->instanceObjectId==1313542167 && $functionData->name=="evClicked")
  {
     // Dann wird beim Dimmer mit ObjectId 1493176580 die Funktion setBrightness mit Parametern brightness=30 und duration=3 aufgerufen werden
  	 executeCommand("1493176580", "setBrightness", array("brightness" => "30","duration" => "3"));
  }
  */
  
  
  myEventOccured($senderData, $receiverData, $functionData);
  triggerEventOccured($senderData, $receiverData, $functionData);
}

/*
Wird einmal pro Minute aufgerufen und kann verwendet werden um regelmäß Dinge zu prüfen oder Aktionen auszulösen.
*/
function timeTrigger()
{
	myTimeTrigger();
}


/* INTERFACE Beschreibung

Alle Funktions- und Parameternamen finden man unter System -> Einstellungen -> FeatureKlassen sobald man die Ansicht auf Entwickler gestellt hat

#############################################################################################################################################
Funktion executeCommand
Sendet einen beliebigen Befehl an das Bussystem
$objectId        ObjectId des Empfängers oder 0 für Broadcast an alle Teilnehmer. Es ist auch möglich an eine bestimmte Teilnehmerklasse zu senden, indem mit der Funktion getObjectId einfach die deviceId und instanceid auf 0 und die classId auf die gewünschte Teilnehmerklasse gesetzt wird
$functionName    Name der Function oder Action die beim Teilnehmer aufgerufen werden soll. Es können aber auch Events oder Results verschickt werden
$paramArray      Assoziatives Array. Key ist die Names des Funktionsparameters und Value der Aufrufwert. Ist der Datentyp eine Enum entspricht der Value dem Namen des Enumwertes!
                 Wenn eine Funktion keine Parameter benötigt, kann ein Leerstring übergeben werden
                 Beispiel:
                 $paramArray["offset"] = 4;
                 $paramArray["data"] = 10;
$resultName      Soll die Funktion ein zugehöriges Ergebnis liefert, muss hier der Name des Ergebnisses angegeben werden oder Leerstring, wenn auf kein Ergebnis gewartet werden soll.
                 Beispiel: Ergebnis des Befehls "Ping" ist "Pong"

return           -1 Timeout beim warten auf das Ergebnos
                 "" Ergebnis wurde Erfolgreich empfangen, aber Ergebnis enthält keine Daten oder es wurde kein Ergebnis erwartet
                 Array mit dem Ergebnis entsprechend vielen Einträgen
                 [0] => stdClass Object            // Element 0
                 (
                    [id] => 369               // DatenbankId dieses Parameters (intern)
                    [name] => relativeHumidity// Name dieses Paramters
                    [type] => WORD            // Datentyp dieses Parameters 
                    [dataValue] => 58         // Wert dieses Parameter (Hier wurde also ein Event verschickt und als Luftfeuchtigkeit 58% gemeldet
                 )

                 [1] => stdClass Object            // Element 1
                 (
                    [id] => 423               // DatenbankId dieses Parameters (intern)
                    [name] => lastEvent       // Name dieses Paramters
                    [type] => ENUM            // Datentyp dieses Parameters 
                    [dataValueName] => CONFORTABLE // Beim Datentyp ENUM zusätzliche Info zum Namen des Parameterwertes. Wert 201 entspricht also CONFORTABLE
                    [dataValue] => 201        // Wert dieses Parameter (Hier der Enumwert für CONFORTABLE)
                 )

function executeCommand($objectId, $functionName, $paramArray="", $resultName="")

                 
Beispiele:

$result = executeCommand("1681719297", "Ping", "", "Pong");
---> Ruft Funktion "Ping" auf Controller mit ObjectId 1681719297 auf und wartet auf Ergebnis "Pong"
---> Ergebnis ist im Erfolgsfall ein Leerstring, weil Pong keine Daten beinhaltet oder -1 wenn keine Antwort kam

$result = executeCommand("1681719297", "getModuleId", array("index" => "INSTALLED"), "ModuleId");
---> Ruft Funktion "getModuleId" auf Controller mit ObjectId 1681719297 und übergibt als Parameter mit Namen "index" den Wert "INSTALLED" (Hier ist der Datentyp von index eine Enum, weshalb der Value der Enumwert als String ist und wartet auf das Ergebnis vom Typ ModuleId
---> Ergebnis Array ( [name] => $MOD$ Booter [size] => 8192 [majorRelease] => 0 [minorRelease] => 75 [firmwareId] => AR8 ) 

$result = executeCommand(0, "ping");
---> verschickt ein Broadcase ping an alle Busteilnehmer und wartet auf kein Ergebnis
---> Alle Controller antworten mit PONG
---> Die letzten beiden Parameter können weggelassen werden, da sie optional sind


#############################################################################################################################################
Funktion setUserData
Mit dieser Funktion kann man sich beliebige Key/Value Paare in der Datenbank merken und damit z.b. Zustände abbilden.
$key			Indentifier/Key des Datensatzes. Maximal 50 Zeichen
$value		Zu speichernder Wert Des Datensatzes. Maximal 255 Zeichen. 		

function setUserData($key, $value);


Beispiel:  setUserData("TemperaturImWohnzimmer","ganz kalt");

Alle in der Datenbank gespeicherten Daten können mit dem Skript editUserPluginData.php angezeigt und gelöscht werden

#############################################################################################################################################
Funktion getUserData
Mit dieser Funktion kann man sich zuvor gemerkte Daten per Key aus der Datenbank lesen
$key			Indentifier/Key des zu lesenden Datensatzes. Maximal 50 Zeichen

return	  Zum Key gespeicherter Wert oder NULL, falls Key nicht in der Datenbank gespeichert.

function getUserData($key);

Beispiel:  getUserData("TemperaturImWohnzimmer"); -> return "ganz kalt"

Alle in der Datenbank gespeicherten Daten können mit dem Skript editUserPluginData.php angezeigt und gelöscht werden

#############################################################################################################################################
Funktion getLastReceivedData
Mit dieser Funktion kann man eine zuletzt empfangene Nachricht von einem Busteilnehmer aus der Datenbank auslesen.
D.h. hier findet keine neue Kommunikation zum Teilnehmer statt, sondern der zuletzt empfangene Wert geliefert.

$objectId        ObjectId des Senders
$functionName    Name des Results oder des Events zu dem die zuletzt empfangenen Daten geliefert werden sollen

return	  Array mit dem Ergebnis entsprechend vielen Einträgen
                 [0] => stdClass Object            // Element 0
                 (
                    [id] => 369               // DatenbankId dieses Parameters (intern)
                    [name] => relativeHumidity// Name dieses Paramters
                    [type] => WORD            // Datentyp dieses Parameters 
                    [dataValue] => 58         // Wert dieses Parameter (Hier wurde also ein Event verschickt und als Luftfeuchtigkeit 58% gemeldet
                 )

                 [1] => stdClass Object            // Element 1
                 (
                    [id] => 423               // DatenbankId dieses Parameters (intern)
                    [name] => lastEvent       // Name dieses Paramters
                    [type] => ENUM            // Datentyp dieses Parameters 
                    [dataValueName] => CONFORTABLE // Beim Datentyp ENUM zusätzliche Info zum Namen des Parameterwertes. Wert 201 entspricht also CONFORTABLE
                    [dataValue] => 201        // Wert dieses Parameter (Hier der Enumwert für CONFORTABLE)
                 )


function getLastReceivedData($objectId, $functionName);

Beispiel:  getLastReceivedData(1948061955,"Status"); -> return 
Array
(
    [brightness] => 0
)

#############################################################################################################################################
Funktion whichIsLastReceivedEvent
Mit dieser Funktion kann man erfragen, welches der übergebenen Events das zuletzt empfangene und in der Datenbank gespeicherte Event eines Senders ist.
Dadurch kann man z.b. rausfinden, ob das aktuelle Zustand eines Aktors gemäß Datenbank z.b. an oder aus sein müsste. (Siehe Beispiel)
Es findet keine neue Kommunikation zum Teilnehmer statt, sondern der zuletzt empfangene Wert geliefert.

$objectId        ObjectId des Senders
$eventNames      Kommaseparierte Liste von Events von denen das jüngst empfangene gemeldet werden soll

return	         Jüngstes Event aus übergebener Liste oder "" falls kein Event gespeichert ist

function whichIsLastReceivedEvent($objectId, $eventNames);

Beispiel:  whichIsLastReceivedEvent(1948061955,"evOn,evOff"); -> return evOf -> Dimmer ist gerade an

#############################################################################################################################################
Funktion userJournal
Mit dieser Funktion kann man eigene Traceausgaben ins Homeserver-Journal schreiben, die dort mit Sender User-Plugin erscheinen
$message				Zu journalisierende Nachricht, die im Journal in der Spalte FUNCTION angezeigt wird
$parameter			optionale Zusatzinformation , die im Journal in der Spalte PARAMETER angezeigt wird 

function userJournal($message, $parameter)


#############################################################################################################################################
Funktion sendEmail
Mit dieser Funktion kann man Emails aus den eigenen Skripten verschicken.
ACHTUNG: Damit die Funktion Emails verschicken kann, muss zunächst ssmtp installiert werden. Siehe http://www.haus-bus.de/install.pdf

$receiver				Empfängeremailadresse
$subject				Betreff der Email
$message				Nachricht (Darf HTML sein)
$from						Absenderemailadresse

function sendEmail($receiver, $subject, $message, $from="reply@domain.com")


#############################################################################################################################################
Funktion getObjectId
Hilfsfunktion, mit der eine Objektid aus den drei Bestandteilen DeviceId, ClassId und InstanceId erzeugt werden kann

function getObjectId($deviceId, $classId, $instanceId)


#############################################################################################################################################
Funktion getClassId
Hilfsfunktion, mit der man die ClassId zu einer Object-ID extrahieren kann, um z.b. rauszufinden, ob der Sender von einem bestimmten Typ ist.
$objectId			Objekt-ID

function getClassId($objectId)

Beispiel: getClassId(1948061779) liefert 16 = Klasse des Tasters (Siehe System -> Einstellungen -> Featureklassen (In Ansicht: Entwickler) -> Taster

*/

// Nicht ändern. Wird vom Cronjob benötigt
if ($argv[1]=="cron")
{
	$_SERVER["DOCUMENT_ROOT"]="../";
  require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");
  myTimeTrigger();
}
else include_once("/var/www/homeserver/plugins/watchdog/trigger.php");

//set_error_handler("myErrorHandler");

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) return;
    userJournal("Fehler im userPlugin:",$errno." ".$errstr." on line $errline in file $errfile");
    return FALSE;
}
?>