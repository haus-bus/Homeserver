<?php

namespace homeserver\apiv1\Utilities;


interface IMessageCounter
{

    /**
     * @return int returns the next message number between 0 and 255
     * @throws \Exception if the database access fails
     */
    public function getNextMessageNumber();

}
