<?php

namespace homeserver\apiv1\Repository;

use DI\FactoryInterface;
use homeserver\apiv1\Model\BaseModel;
use homeserver\apiv1\Utilities\Convert;
use homeserver\apiv1\Utilities\DbAccess;
use phpDocumentor\Parser\Exception;

abstract class BaseRepository
{

    /**
     * @Inject
     * @var DbAccess the object to access the database
     */
    protected $db;

    /**
     * @var string name of the db table
     */
    protected $tableName;

    /**
     * @Inject
     * @var FactoryInterface $factory the factory to create instances
     */
    protected $factory;

    /**
     ** @param int $id The ID of the requested object
     * @return BaseModel The requested object by ID
     * @throws \Exception if the data source could not be accessed or the instances could not be created
     */
    public function getById($id)
    {
        $result = $this->getByIds(array($id));
        return count($result) > 0 ? $result[0] : null;
    }

    /**
     ** @param int[] $ids The IDs of the requested objects
     * @return BaseModel[] The requested objects by IDs
     * @throws \Exception if the data source could not be accessed or the instances could not be created
     */
    public function getByIds($ids)
    {
        if ($ids == null || count($ids) == 0) {
            throw new \Exception("the parameter \$ids must contain at least one id");
        }
        $ids = array_filter($ids, function ($val) {
            return $val != null && is_numeric($val);
        });
        if (count($ids) == 0) {
            throw new \Exception("the parameter \$ids must contain at least one id");
        }

        $tableName = $this->tableName;
        $dbResult = $this->db->select("SELECT * FROM $tableName WHERE id IN (" . implode(',', $ids) . ")");
        $result = [];
        foreach ($dbResult as $row) {
            array_push($result, $this->createInstance($row));
        }
        return $result;
    }

    /**
     * @return BaseModel[] Returns all objects
     * @throws \Exception if the data source could not be accessed or the instances could not be created
     */
    public function getAll()
    {
        $tableName = $this->tableName;
        $dbResult = $this->db->select("SELECT * FROM $tableName");
        $result = [];
        foreach ($dbResult as $row) {
            array_push($result, $this->createInstance($row));
        }
        return $result;
    }

    /**
     * @param $params array an associative array containing the property names as key and the operator and parameter separated by blank as value
     * @param $returnRaw bool if true, the dbResult will be returned directly without creating class instances
     * @param $columns array the column names to select
     * @return BaseModel[] | array Returns the matching objects for the given query or the mysqli dbResult
     * @throws \Exception if the data source could not be accessed or the instances could not be created
     */
    public function query($params, $returnRaw = false, array $columns = null)
    {
        if (count($params) <= 0) {
            return $this->getAll();
        }
        $tableName = $this->tableName;

        // prepare and execute statement
        $colNames = '*';
        if ($columns != null && count($columns) > 0) {
            $colNames = implode(', ', $columns);
        }
        $sql = "select $colNames from $tableName where " . $this->ConvertToQueryParams($params, "and");
        $dbResult = $this->db->query($sql);

        // create instances
        $result = [];
        if ($returnRaw) {
            foreach ($dbResult as $row) {
                $result[] = $row;
            }
        } else {
            foreach ($dbResult as $row) {
                $result[] = $this->createInstance($row);
            }
        }
        return $result;
    }

    /**
     * Creates new objects and stores them in the database
     *
     * @param $objects array the objects to add to the repository
     * @return BaseModel[] the created and stored objects
     * @throws \Exception if the database access fails
     */
    public function add(array $objects)
    {
        $tableName = $this->tableName;

        $statements = [];
        foreach ($objects as $object) {
            $params = get_object_vars($object);
            if (array_key_exists("id", $params) && $params["id"] > 0) {
                throw new \Exception("It's not possible to create an object that has already an id");
            }

            // filter given properties
            $params = array_filter($params, function ($key) {
                // exclude lastModified and created, they are managed by the API or database
                // exclude default properties from the base class if they are not existent in the database
                return !($key == "lastModified" || $key == "created" || $key == "id" ||
                    (array_key_exists($key, BaseModel::getDefaultProperties()) && !$this->db->colExists($this->tableName, $key)));
            }, ARRAY_FILTER_USE_KEY);

            // define created
            if ($this->db->colExists($tableName, 'created')) {
                $params['created'] = Convert::formatTime(new \DateTime());
            }

            // convert and quote values
            $aliasNames = [];
            array_walk($params,
                function (&$val, $key) use (&$aliasNames) {
                    $oldKey = $key;
                    $this->db->convertValue($this->tableName, $key, $val);
                    if ($oldKey != $key) {
                        $aliasNames[$oldKey] = $key;
                    }
                });
            foreach ($aliasNames as $property => $col) {
                $params[$col] = $params[$property];
                unset($params[$property]);
            }

            // prepare statement
            $colNames = implode(', ', array_keys($params));
            $colValues = implode(', ', array_values($params));
            $statements[] = "INSERT INTO $tableName ($colNames) VALUES ($colValues)";
        }

        // execute statements
        $ids = [];
        foreach ($statements as $sql) {
            $ids[] = $this->db->query($sql);
        }

        // return new objects
        return $this->getByIds($ids);
    }

    /**
     * Updates the objects
     *
     * @param object[] $objects the objects which will be updated
     * @param array $propertyNames the property names to update. If defined, only this properties will be updated
     * @return BaseModel[] the updated objects
     * @throws \Exception if the database access fails
     */
    public function update($objects, $propertyNames = array())
    {
        $tableName = $this->tableName;

        $statements = [];
        $ids = [];
        foreach ($objects as $object) {
            // get all properties of the object
            $params = get_object_vars($object);

            // get the object id
            if (!array_key_exists("id", $params)) {
                throw new \Exception("It's not possible to update an object that has no id");
            }
            $id = $object->id;
            unset($params['id']);

            // filter given properties
            $params = array_filter($params, function ($key) use ($propertyNames) {
                // exclude lastModified and created, they are managed by the API or database
                // exclude default properties from the base class if they are not existent in the database
                $include = !($key == "lastModified" || $key == "created" || $key == "createdBy" ||
                    (array_key_exists($key, BaseModel::getDefaultProperties()) && !$this->db->colExists($this->tableName, $key)));

                // filter update properties if needed
                if ($include && $propertyNames != null && count($propertyNames) > 0) {
                    return in_array($key, $propertyNames);
                }

                return $include;
            }, ARRAY_FILTER_USE_KEY);

            // define lastModified
            if ($this->db->colExists($tableName, 'lastModified')) {
                $params['lastModified'] = Convert::formatTime(new \DateTime());
            }

            // convert and quote values
            array_walk($params,
                function (&$val, $key) {
                    $this->db->convertValue($this->tableName, $key, $val);
                    $val = "$key=" . $val;
                });
            $statements[] = "UPDATE $tableName SET " . implode(", ", $params) . " WHERE id = $id";
            $ids[] = $id;
        }

        // execute statements
        foreach ($statements as $sql) {
            $this->db->query($sql);
        }

        // return updated objects
        return $this->getByIds($ids);
    }

    /**
     * Deletes all objects that are matching the parameters
     *
     * @param $params array an associative array containing the property names as key and the operator and parameter separated by blank as value
     * @return BaseModel[] the deleted objects
     * @throws \Exception if the database access fails or the property names don't exist
     * @example The params array could be look like this: ['id' => '> 5', 'id' => '<= 10']. The function would delete all objects with an id greater than 5 and less or equal than 10.
     */
    public function deleteByQuery($params)
    {
        if (count($params) <= 0) {
            throw new \Exception("deleteByQuery without parameters is not allowed");
        }
        $tableName = $this->tableName;

        // get the deleted objects
        $deleted = $this->query($params);

        // prepare and execute statement
        $sql = "delete from $tableName where " . $this->ConvertToQueryParams($params, "and");
        $this->db->query($sql);

        return $deleted;
    }

    /**
     * Deletes all objects according to the given ids
     *
     * @param $ids array an array containing the ids of the objects to delete
     * @return BaseModel[] the deleted objects
     * @throws \Exception if the database access fails
     */
    public function deleteByIds($ids)
    {
        if (count($ids) <= 0) {
            return array();
        }
        $tableName = $this->tableName;

        $deleted = $this->getByIds($ids);

        // execute statement
        $this->db->query("delete from $tableName where id in (" . implode(", ", $ids) . ")");
        return $deleted;
    }

    /**
     * @return string the database table name
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param array $row the fetched row from the database
     * @return BaseModel the object instance
     */
    abstract protected function createInstance(array $row);

    /**
     * Takes an associative array and returns a query string that ca be used directly in a where-clause.
     *
     * @param $params array the key must be the name of the database column. The value must start with the operand (=, !=, >, <, ...) followed by a space and the value
     * @param $operand string the operand to use for gluing the parameters together (and, or, ...)
     * @return string returns a where clause containing all parameters glued by the operand
     */
    private function ConvertToQueryParams($params, $operand)
    {
        array_walk($params,
            function (&$val, $key) use ($operand) {

                if (is_array($val) && count($val) > 0) {
                    // multiple values for one property
                    $multipleValues = array();

                    foreach ($val as $v) {
                        // determine operator and value
                        $v = trim($v);
                        $firstBlank = strpos($v, ' ');
                        if ($firstBlank < 0) {
                            throw new Exception("the param value must consist of an operator and its value separated by a blank");
                        }
                        $operator = substr($v, 0, $firstBlank + 1);
                        $par = substr($v, $firstBlank + 1);

                        // convert param
                        $this->db->convertValue($this->tableName, $key, $par);
                        $multipleValues[] = "$key " . $operator . $par;
                    }

                    $val = implode(" $operand ", $multipleValues);
                } elseif (is_string($val)) {
                    // determine operator and value
                    $val = trim($val);
                    $firstBlank = strpos($val, ' ');
                    if ($firstBlank < 0) {
                        throw new Exception("the param value must consist of an operator and its value separated by a blank");
                    }
                    $operator = substr($val, 0, $firstBlank + 1);
                    $par = substr($val, $firstBlank + 1);

                    // convert param
                    $this->db->convertValue($this->tableName, $key, $par);
                    $val = "$key " . $operator . $par;
                } else {
                    throw new Exception("the param array must contain a string or an array. '$val' is not allowed");
                }
            });
        return implode(" $operand ", array_values($params));
    }

}
