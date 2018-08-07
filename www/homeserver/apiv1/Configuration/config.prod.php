<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/dbconnect.php");

return [
    'db.host' => $server,
    'db.name' => $datenbank,
    'db.user' => $dbuser,
    'db.password' => $dbpasswort,
    'db.schema' => $dbuser,
];
