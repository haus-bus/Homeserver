<?php

namespace homeserver\apiv1\Model;


use homeserver\apiv1\Utilities\Convert;

class BaseModel implements \JsonSerializable
{
    /**
     * @var int $id ID of the object
     */
    protected $id;

    /**
     * @var string $name The name of the object
     */
    protected $name;

    /**
     * @var string $description The description of the object
     */
    protected $description;

    /**
     * @var \DateTime $created When the object was created
     */
    protected $created;

    /**
     * @var \DateTime $lastModified When the object was modified at last
     */
    protected $lastModified;

    /**
     * @var string $createdBy Name or ID of the creator of this object
     */
    protected $createdBy;

    /**
     * @var string $modifiedBy Name or ID of the last modifier of this object
     */
    protected $modifiedBy;


    /**
     * BaseModel constructor.
     * @param int $id
     * @param string $name
     * @param string $description
     */
    public function __construct($id, $name = null, $description = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->created = new \DateTime(null, new \DateTimeZone("Europe/Berlin"));
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @param \DateTime $lastModified
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
    }

    /**
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param string $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return string
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * @param string $modifiedBy
     */
    public function setModifiedBy($modifiedBy)
    {
        $this->modifiedBy = $modifiedBy;
    }

    public function __toString()
    {
        $format = '[%d] %s; %s';
        return sprintf($format, $this->id, $this->name, $this->description);
    }


    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "created" => Convert::formatTime($this->created),
            "lastModified" => Convert::formatTime($this->lastModified),
            "createdBy" => $this->createdBy,
            "modifiedBy" => $this->modifiedBy
        ];
    }


    /**
     * @return array returns the default properties in an associated array with the property names as key
     */
    public static function getDefaultProperties() {
        return get_class_vars(__CLASS__);
    }
}
