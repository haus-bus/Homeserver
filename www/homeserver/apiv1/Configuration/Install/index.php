<?php
/**
 * This script will install the api and prepare it for the usage
 */

// TODO: check installed php extensions

// clear the cache
apcu_clear_cache();
echo "Cache cleared<br>";

// set configuration dir
define("CONFIG_DIR", '../');

// initialize container
$container = require CONFIG_DIR . 'container.php';

// read database information
$db = $container->get(\homeserver\apiv1\Utilities\DbAccess::class);
$dbInfoRaw = $db->select("select table_name, column_name, data_type from INFORMATION_SCHEMA.COLUMNS where table_schema = '" . $db->getSchema() . "' order by table_name");
$dbAlias = require 'dbAlias.php';

// write db config file
foreach ($dbInfoRaw as $row) {
    $dbInfo[$row['table_name']][$row['column_name']]['dataType'] = $row['data_type'];
    $dbInfo[$row['table_name']][$row['column_name']]['alias'] = $dbAlias[$row['table_name']][$row['column_name']];
}
$dbConfStr = "<?php \nreturn [\n";
foreach ($dbInfo as $key => $table) {
    $dbConfStr .= "\t'$key' => [\n";
    foreach ($table as $colName => $colDetails) {
        $dbConfStr .= "\t\t'$colName' => [\n";
        foreach ($colDetails as $info => $val) {
            $dbConfStr .= "\t\t\t'$info' => '$val',\n";
        }
        $dbConfStr .= "\t\t],\n";
    }
    $dbConfStr .= "\t],\n";
}
$dbConfStr .= "\n];";

$handle = fopen(CONFIG_DIR . 'config.dbInfo.php', 'w');

if (!$handle) {
    echo "Can't open the config file '" . CONFIG_DIR . "config.dbInfo.php' \n";
    return;
}

$success = fwrite($handle, $dbConfStr);
fclose($handle);

if (!$success) {
    echo "An error occurred while writing the config file 'config.dbInfo.php' \n";
    return;
}

echo "Database info file written successfully... \n";
