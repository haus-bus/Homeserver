<?php

namespace homeserver\apiv1\Utilities;

use DI\Annotation\Inject;

class DbAccess
{
    private $host;
    private $user;
    private $name;
    private $password;
    private $schema;

    /**
     * @Inject("dbInfo")
     * @var array $dbInfo holds information about the database structure
     */
    private $dbInfo;

    /**
     * @var \mysqli $connection The database connection
     */
    protected static $connection;

    /**
     * Db constructor.
     *
     * @param $host string the database server
     * @param $user string the database user
     * @param $name string the database name
     * @param $password string the password to connect to the database
     * @param $schema string the schema that contains all necessary tables
     */
    public function __construct($host, $user, $name, $password, $schema)
    {
        $this->host = $host;
        $this->user = $user;
        $this->name = $name;
        $this->password = $password;
        $this->schema = $schema;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @param string $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * Connect to the database
     *
     * @return \mysqli mysqli MySQLi object instance on success
     * @throws \Exception if the database connection fails
     */
    public function connect()
    {
        // Try and connect to the database
        if (!isset(self::$connection)) {
            self::$connection = new \mysqli($this->host, $this->user, $this->password, $this->name);
        }

        // If connection was not successful
        if (self::$connection === false) {
            throw new \Exception("Database connection could not be established");
        }
        return self::$connection;
    }

    /**
     * Query the database
     *
     * @param string $query The query string
     * @return mixed|int The result of the mysqli::query() function or the autoincrement id number after an insert or the number of the affected rows after an update or delete
     * @throws \Exception if the database connection fails
     */
    public function query($query)
    {
        // Connect and query
        $connection = $this->connect();
        $result = $connection->query($query);

        if ($result === true) {
            switch (strtoupper(substr(trim($query), 0, 6))) {
                case 'INSERT':
                    $result = $connection->insert_id;
                    break;

                case 'UPDATE':
                case 'DELETE':
                    $result = $connection->affected_rows;
                    break;
            }
        }
        return $result;
    }

    /**
     * Fetch rows from the database (SELECT query)
     *
     * @param string $query The query string
     * @return array Database rows
     * @throws \Exception if the db access fails
     */
    public function select($query)
    {
        $rows = array();
        $result = $this->query($query);
        if ($result === false) {
            throw new \Exception(self::$connection->connect_error . ": $query");
        }
        while ($row = $result->fetch_assoc()) {
            // fixme: encoding
            if (array_key_exists('name', $row)) {
                $row['name'] = mb_convert_encoding($row['name'], "UTF-8");
            }
            if (array_key_exists('comment', $row)) {
                $row['comment'] = mb_convert_encoding($row['comment'], "UTF-8");
            }
            if (array_key_exists('title', $row)) {
                $row['title'] = mb_convert_encoding($row['title'], "UTF-8");
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Fetch the last error from the database
     *
     * @return string Database error message
     */
    public function getError()
    {
        return self::$connection != null ? self::$connection->error : "";
    }

    /**
     * Quote and escape value for use in a database query
     *
     * @param string $value The value to be quoted and escaped
     * @return string The quoted and escaped string
     * @throws \Exception if database connection fails
     */
    public function quote($value)
    {
        $connection = $this->connect();
        return "'" . $connection->real_escape_string($value) . "'";
    }

    /**
     * Converts a value dependant on the column type to a string that can be used in a query
     *
     * @param $tableName string the name of the database table
     * @param $columnName string the name of the database column or the property name by reference. If the property name is giben, it will be converted to the database column name
     * @param $value string the value to convert by reference
     * @throws \Exception if database connection fails
     */
    public function convertValue($tableName, &$columnName, &$value)
    {
        if (!array_key_exists($tableName, $this->dbInfo)) {
            throw new \Exception("the cached database information contains no infos for table '$tableName'. Did you run install.php?");
        }

        if (!array_key_exists($columnName, $this->dbInfo[$tableName])) {
            $realColName = "";
            foreach ($this->dbInfo[$tableName] as $col => $details) {
                if ($details['alias'] == $columnName) {
                    $realColName = $col;
                    break;
                }
            }

            if ($realColName == "") {
                throw new \Exception("the cached database information contains no infos for table '$tableName' and column '$columnName'. Did you run install.php?");
            }
            $columnName = $realColName;
        }
        $type = $this->dbInfo[$tableName][$columnName]['dataType'];
        switch ($type) {
            case "text":
            case "varchar":
            case "timestamp":
                $value = $this->quote($value);
        }
    }

    /**
     * Checks if the column exists
     *
     * @param $tableName string the name of the database table
     * @param $columnName string the name of the database column or the object property name
     * @return bool true if the column exists
     */
    public function colExists($tableName, $columnName) {
        // check the database column name
        $colExists = array_key_exists($tableName, $this->dbInfo) && array_key_exists($columnName, $this->dbInfo[$tableName]);
        if ($colExists) {
            return true;
        }

        // check the property names
        foreach ($this->dbInfo[$tableName] as $col => $details) {
            if ($details['alias'] == $columnName) {
                return true;
            }
        }

        return false;
    }
}
