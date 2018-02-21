<?php

namespace homeserver\apiv1\Utilities;


interface ICommunicator
{

    /**
     * @param int $receiverObjectId id of the object which will receive this message
     * @param array $data the data bytes to send
     * @param int $senderObjectId the sender id of this message. If not defined, the id will be set automatically
     */
    public function sendData($receiverObjectId, $data, $senderObjectId = 0);

}
