<?php
/*
Mit dieser Klasse lassen sich eigene Plugins f�r das Bussystem schreiben.
Man wird �ber alle eintreffenden Nachrichten informiert und kann beliebige Aktionen ausl�sen.
Zus�tzlich gibt es Hilfsfunktionen, mit denen man sich Zust�nde merken kann.

ACHTUNG: Das userPlugin muss einmalig aktiviert werden:
1. per SSH einloggen
2. sudo php /var/www/homeserver/activateUserPlugin.php

ACHTUNG: Nicht diese Datei �ndern, sondern die Datei myUserPlugin.php, da diese Datei mit jedem Update �berschrieben wird !!

ACHTUNG: Jedes mal, wenn dieses Skript ge�ndert wird, muss es einmal neu eingelesen werden.
Dies kann man erledigen, indem in der Homeserveroberfl�che beim Raspberry -> Executor die Funktion reloadUserPlugin aufgerufen wird

*/

include_once("/var/www/homeserver/user/myUserPlugin.php"); 


/*
 Wird bei jedem eintreffenden Event aufgerufen.
 Achtung: Funktion l�uft im Prozess, der die UDP Daten verarbeitet. Bitte keine Langlaufaktionen oder sleeps verwenden.
*/
function eventOccured($senderData, $receiverData, $functionData)
{
	/* 
	Beschreibung $senderData und $receiverData Objekt	
	Die dbIDs sind eigentlich nur intern und sollten nicht verwendet werden
	ACHTUNG: Wenn es sich bei dem Event um einen Broadcast handelt, ist in $receiverData instanceObjectId=0 und der Rest leer!
	stdClass Object
  (
    [instanceObjectId] => 1493181016          // ObjektId des Eventsenders bzw. Empf�ngers (Bei Broadcast ist der Empf�nger 0)
    [instanceDbId] => 10275                   // DatenbankId des Eventsenders bzw. Empf�ngers (intern)
    [instanceName] => Feuchtesensor K�che     // Vom Benutzer vergebener Name des Eventsenders bzw. Empf�ngers
    [classId] => 34                           // ClassId der Kasse des Eventsenders bzw. Empf�ngers
    [classDbId] => 23                         // DatenbankId der Klasse des Eventsenders bzw. Empf�ngers (intern)
    [className] => Feuchtesensor              // Name der Klasse des Eventsenders bzw. Empf�ngers
    [controllerObjectId] => 1493172225        // ObjektId des versendenden bzw. empfangenden Controllers (Also der Controller auf dem die Sender- oder Empf�ngerinstanz l�uft)
    [controllerDbId] => 3                     // DatenbankId des versendenden bzw. empfangenden Controllers (intern)
    [controllerName] => Herms Ecke            // Vom Benutzer vergebener Name des versendenden bzw. empfangenden Controllers
    [roomDbId] => 8                           // DatenbankId des ersten Raums, dem der Sender bzw. Empf�ngers oder leer, wenn keinem Raum zugeordnet
    [roomName] => K�che                       // Vom Benutzer vergebener Name des ersten Raums, dem der Sender bzw. Empf�ngers oder leer, wenn keinem Raum zugeordnet
 )
 
 Beschreibung $functionData
 stdClass Object
 ( 
    [functionId] => 129                       // Beim Eventtyp ACTION oder FUNCTION (siehe type) ist dies die FunktionsId der Funktion, die beim Empf�nger aufgerufen wird
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
                    [dataName] => CONFORTABLE // Beim Datentyp ENUM zus�tzliche Info zum Namen des Parameterwertes. Wert 201 entspricht also CONFORTABLE
                    [dataValue] => 201        // Wert dieses Parameter (Hier der Enumwert f�r CONFORTABLE)
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
}

/*
Wird einmal pro Minute aufgerufen und kann verwendet werden um regelm�� Dinge zu pr�fen oder Aktionen auszul�sen.
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
$objectId        ObjectId des Empf�ngers oder 0 f�r Broadcast an alle Teilnehmer. Es ist auch m�glich an eine bestimmte Teilnehmerklasse zu senden, indem mit der Funktion getObjectId einfach die deviceId und instanceid auf 0 und die classId auf die gew�nschte Teilnehmerklasse gesetzt wird
$functionName    Name der Function oder Action die beim Teilnehmer aufgerufen werden soll. Es k�nnen aber auch Events oder Results verschickt werden
$paramArray      Assoziatives Array. Key ist die Names des Funktionsparameters und Value der Aufrufwert. Ist der Datentyp eine Enum entspricht der Value dem Namen des Enumwertes!
                 Wenn eine Funktion keine Parameter ben�tigt, kann ein Leerstring �bergeben werden
                 Beispiel:
                 $paramArray["offset"] = 4;
                 $paramArray["data"] = 10;
$resultName      Soll die Funktion ein zugeh�riges Ergebnis liefert, muss hier der Name des Ergebnisses angegeben werden oder Leerstring, wenn auf kein Ergebnis gewartet werden soll.
                 Beispiel: Ergebnis des Befehls "Ping" ist "Pong"

return           -1 Timeout beim warten auf das Ergebnos
                 "" Ergebnis wurde Erfolgreich empfangen, aber Ergebnis enth�lt keine Daten oder es wurde kein Ergebnis erwartet
                 Array mit dem Ergebnis entsprechend vielen Eintr�gen
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
                    [dataValueName] => CONFORTABLE // Beim Datentyp ENUM zus�tzliche Info zum Namen des Parameterwertes. Wert 201 entspricht also CONFORTABLE
                    [dataValue] => 201        // Wert dieses Parameter (Hier der Enumwert f�r CONFORTABLE)
                 )

function executeCommand($objectId, $functionName, $paramArray="", $resultName="")

                 
Beispiele:

$result = executeCommand("1681719297", "Ping", "", "Pong");
---> Ruft Funktion "Ping" auf Controller mit ObjectId 1681719297 auf und wartet auf Ergebnis "Pong"
---> Ergebnis ist im Erfolgsfall ein Leerstring, weil Pong keine Daten beinhaltet oder -1 wenn keine Antwort kam

$result = executeCommand("1681719297", "getModuleId", array("index" => "INSTALLED"), "ModuleId");
---> Ruft Funktion "getModuleId" auf Controller mit ObjectId 1681719297 und �bergibt als Parameter mit Namen "index" den Wert "INSTALLED" (Hier ist der Datentyp von index eine Enum, weshalb der Value der Enumwert als String ist und wartet auf das Ergebnis vom Typ ModuleId
---> Ergebnis Array ( [name] => $MOD$ Booter [size] => 8192 [majorRelease] => 0 [minorRelease] => 75 [firmwareId] => AR8 ) 

$result = executeCommand(0, "ping");
---> verschickt ein Broadcase ping an alle Busteilnehmer und wartet auf kein Ergebnis
---> Alle Controller antworten mit PONG
---> Die letzten beiden Parameter k�nnen weggelassen werden, da sie optional sind


#############################################################################################################################################
Funktion setUserData
Mit dieser Funktion kann man sich beliebige Key/Value Paare in der Datenbank merken und damit z.b. Zust�nde abbilden.
$key			Indentifier/Key des Datensatzes. Maximal 50 Zeichen
$value		Zu speichernder Wert Des Datensatzes. Maximal 255 Zeichen. 		

function setUserData($key, $value);


Beispiel:  setUserData("TemperaturImWohnzimmer","ganz kalt");

Alle in der Datenbank gespeicherten Daten k�nnen mit dem Skript editUserPluginData.php angezeigt und gel�scht werden

#############################################################################################################################################
Funktion getUserData
Mit dieser Funktion kann man sich zuvor gemerkte Daten per Key aus der Datenbank lesen
$key			Indentifier/Key des zu lesenden Datensatzes. Maximal 50 Zeichen

return	  Zum Key gespeicherter Wert oder NULL, falls Key nicht in der Datenbank gespeichert.

function getUserData($key);

Beispiel:  getUserData("TemperaturImWohnzimmer"); -> return "ganz kalt"

Alle in der Datenbank gespeicherten Daten k�nnen mit dem Skript editUserPluginData.php angezeigt und gel�scht werden

#############################################################################################################################################
Funktion getLastReceivedData
Mit dieser Funktion kann man eine zuletzt empfangene Nachricht von einem Busteilnehmer aus der Datenbank auslesen.
D.h. hier findet keine neue Kommunikation zum Teilnehmer statt, sondern der zuletzt empfangene Wert geliefert.

$objectId        ObjectId des Senders
$functionName    Name des Results oder des Events zu dem die zuletzt empfangenen Daten geliefert werden sollen

return	  Array mit dem Ergebnis entsprechend vielen Eintr�gen
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
                    [dataValueName] => CONFORTABLE // Beim Datentyp ENUM zus�tzliche Info zum Namen des Parameterwertes. Wert 201 entspricht also CONFORTABLE
                    [dataValue] => 201        // Wert dieses Parameter (Hier der Enumwert f�r CONFORTABLE)
                 )


function getLastReceivedData($objectId, $functionName);

Beispiel:  getLastReceivedData(1948061955,"Status"); -> return 
Array
(
    [brightness] => 0
)

#############################################################################################################################################
Funktion whichIsLastReceivedEvent
Mit dieser Funktion kann man erfragen, welches der �bergebenen Events das zuletzt empfangene und in der Datenbank gespeicherte Event eines Senders ist.
Dadurch kann man z.b. rausfinden, ob das aktuelle Zustand eines Aktors gem�� Datenbank z.b. an oder aus sein m�sste. (Siehe Beispiel)
Es findet keine neue Kommunikation zum Teilnehmer statt, sondern der zuletzt empfangene Wert geliefert.

$objectId        ObjectId des Senders
$eventNames      Kommaseparierte Liste von Events von denen das j�ngst empfangene gemeldet werden soll

return	         J�ngstes Event aus �bergebener Liste oder "" falls kein Event gespeichert ist

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
ACHTUNG: Damit die Funktion Emails verschicken kann, muss zun�chst ssmtp installiert werden. Siehe http://www.haus-bus.de/install.pdf

$receiver				Empf�ngeremailadresse
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

// Nicht �ndern. Wird vom Cronjob ben�tigt
if ($argv[1]=="cron")
{
	$_SERVER["DOCUMENT_ROOT"]="../";
  require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");
  myTimeTrigger();
}

//set_error_handler("myErrorHandler");

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) return;
    userJournal("Fehler im userPlugin:",$errno." ".$errstr." on line $errline in file $errfile");
    return FALSE;
}
?>