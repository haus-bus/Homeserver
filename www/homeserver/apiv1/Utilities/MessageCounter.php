<?php

namespace homeserver\apiv1\Utilities;


class MessageCounter implements IMessageCounter
{
    /**
     * @Inject
     * @var DbAccess the object to access the database
     */
    protected $db;

    /**
     * @Inject("tablenames.udpHelper")
     * @var string name of the db table
     */
    protected $tableName;

    /**
     * @return int returns the next message number between 0 and 255
     * @throws \Exception if the database access fails
     */
    public function getNextMessageNumber()
    {
        return $this->db->query("INSERT INTO udpHelper (dummy) VALUES('1')") % 255;
    }
}
