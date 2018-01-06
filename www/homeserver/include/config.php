<?php

// Offizielle Parameter - Bei Bedarf �ndern
$SERVER_ACTIVE = 1;        // PC-Serverinstanz aktiv

/*$SERVER_WEATHER = 1;      // Wetterfunktionen auf dem Server aktivieren (Ben�tigt Internetzugriff)
$COUNTRY = "Deutschland"; // Land f�r automatische Tag/Nacht Steuerung nach D�mmerungszeiten
$ZIP_CODE = "33102";      // PLZ f�r automatische Tag/Nacht Steuerung nach D�mmerungszeiten
*/

$MAX_LOG_ENTRIES = 2000000; // Wie viele Datenbankeintr�ge maximal im Journal gehalten werden
$MAX_TRACE_ENTRIES = 10000; // Wie viele Datenbankeintr�ge maximal im Trace gehalten werden


// INTERNE PARAMETER -- HIER NICHTS �NDERN !
$UDP_PORT = 9;
$UDP_BCAST_IP = "255.255.255.255";
$MY_OBJECT_ID = (9999<<16)+1;
$UDP_HEADER_BYTES= array(0xef,0xef);
$BROADCAST_OBJECT_ID = 0;
$SIMULATOR_ACTIVE = 0;
$CONTROLLER_READ_TIMEOUT=1;

$EVENTS_START=200;
$RESULT_START=128;

$CONTROLLER_CLASSES_ID = 12;
$CONTROLLER_CLASS_ID = 0;
$FIRMWARE_INSTANCE_ID = 1;
$BOOTLOADER_INSTANCE_ID = 2;
$signalParamWildcard="255";
$showGeneratedGroups="auto"; // auto = In der Entwicklersicht aktiv
?>