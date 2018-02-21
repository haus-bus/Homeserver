<?php

namespace homeserver\apiv1\Utilities;


use homeserver\apiv1\Model\Parameters\ParamDword;
use homeserver\apiv1\Model\Parameters\ParamWord;
use homeserver\apiv1\Repository\BasicConfigRepository;

class Communicator implements ICommunicator
{
    /**
     * @var int $udpPort the UDP port to use
     */
    private $udpPort;

    /**
     * @var int $myObjectId the default sender object id when data is sent
     */
    private $myObjectId;

    /**
     * @var array $udpHeaderBytes the header bytes
     */
    private $udpHeaderBytes;

    /**
     * @var IMessageCounter $messageCounter the message counter to get continuous numbers
     */
    private $messageCounter;

    /**
     * @Inject
     * @var BasicConfigRepository $confRepository to resolve configurations
     */
    private $confRepository;


    /**
     * Communicator constructor.
     * @param int $udpPort the UDP port to use
     * @param int $myObjectId the default sender object id when data is sent
     * @param array $udpHeaderBytes the header bytes
     * @param IMessageCounter $messageCounter the message counter to get continuous numbers
     */
    public function __construct($udpPort, $myObjectId, $udpHeaderBytes, $messageCounter)
    {
        $this->udpPort = $udpPort;
        $this->myObjectId = $myObjectId;
        $this->udpHeaderBytes = $udpHeaderBytes;
        $this->messageCounter = $messageCounter;
    }

    /**
     * @return int the UDP port to use
     */
    public function getUdpPort()
    {
        return $this->udpPort;
    }

    /**
     * @param int $udpPort the UDP port to use
     */
    public function setUdpPort($udpPort)
    {
        $this->udpPort = $udpPort;
    }

    /**
     * @return int the default sender object id when data is sent
     */
    public function getMyObjectId()
    {
        return $this->myObjectId;
    }

    /**
     * @param int $myObjectId the default sender object id when data is sent
     */
    public function setMyObjectId($myObjectId)
    {
        $this->myObjectId = $myObjectId;
    }

    /**
     * @return array the header bytes
     */
    public function getUdpHeaderBytes()
    {
        return $this->udpHeaderBytes;
    }

    /**
     * @param array $udpHeaderBytes the header bytes
     */
    public function setUdpHeaderBytes($udpHeaderBytes)
    {
        $this->udpHeaderBytes = $udpHeaderBytes;
    }


    /**
     * @param int $receiverObjectId id of the object which will receive this message
     * @param array $data the data bytes to send
     * @param int $senderObjectId the sender id of this message. If not defined, the id will be set automatically
     * @throws \Exception if something goes wrong
     */
    public function sendData($receiverObjectId, $data, $senderObjectId = 0)
    {

        if ($senderObjectId == 0)
            $senderObjectId = $this->myObjectId;

        $datagrammPos = 0;

        // UDP-Header
        foreach ($this->udpHeaderBytes as $value) {
            $datagramm[$datagrammPos++] = $value;
        }

        // check-Byte
        $datagramm[$datagrammPos++] = 0x00;

        // message counter
        $datagramm[$datagrammPos++] = $this->messageCounter->getNextMessageNumber();

        // sender-ID
        $dWordParam = new ParamDword(-1);
        foreach ($dWordParam->getBytes($senderObjectId) as $value) {
            $datagramm[$datagrammPos++] = $value;
        }

        // receiver-ID
        foreach ($dWordParam->getBytes($receiverObjectId) as $value) {
            $datagramm[$datagrammPos++] = $value;
        }

        // data length
        $dataLength = 0;
        foreach ($data as $datum) {
            $dataLength += count($datum);
        }
        $wordParam = new ParamWord(-1);
        $dataLengthBytes = $wordParam->getBytes($dataLength);
        foreach ($dataLengthBytes as $value) {
            $datagramm[$datagrammPos++] = $value;
        }

        // data (contains a byte array for each parameter)
        foreach ($data as $datum) {
            // collect every single byte
            foreach ($datum as $d) {
                $datagramm[$datagrammPos++] = $d;
            }
        }

        $binaryMsg = "";
        for ($i = 0; $i < $datagrammPos; $i++) {
            $binaryMsg .= chr($datagramm[$i]);
        }

        if ($this->isWindows()) {
            $fp = fsockopen("udp://" . $this->getBroadcastNetworkIp(), $this->udpPort, $errno, $errstr);
            fwrite($fp, $binaryMsg, $datagrammPos);
        } else {
            $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($s == false) {
                throw new \Exception("Error creating socket! Error code is '"
                    . socket_last_error($s) . "' - " . socket_strerror(socket_last_error($s)));
            } else {
                // setting a broadcast option to socket:
                $opt_ret = socket_set_option($s, 1, 6, true);
                if ($opt_ret < 0) {
                    throw new \Exception("setsockopt() failed, error: " . $opt_ret);
                }
                socket_sendto($s, $binaryMsg, $datagrammPos, 0, $this->getBroadcastNetworkIp(), $this->udpPort);
                socket_close($s);
            }
        }
    }

    /**
     * @return bool true if the os is windows
     * @throws \Exception if the os could not be determined
     */
    private function isWindows()
    {
        ob_start();
        phpInfo(INFO_GENERAL);
        $pinfo = ob_get_contents();
        ob_end_clean();
        $pos = strpos($pinfo, "System");
        if ($pos === "FALSE") {
            throw new \Exception("Betriebssystem konnte nicht ermittelt werden");
        } else {
            $check = strtolower(substr($pinfo, $pos, strpos($pinfo, "</tr>", $pos) - $pos));
            return strpos($check, "windows") !== false;
        }
    }


    /**
     * Returns the Broadcast IP address
     *
     * @return string the broadcast ip address
     * @throws \Exception if the configuration could not be accessed
     */
    private function getBroadcastNetworkIp() {
        $conf = $this->confRepository->getById(45);
        if ($conf == null || $conf->getValue() == "")
            return '255.255.255.255';
        else
            return $conf->getValue();
    }
}
