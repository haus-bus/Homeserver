<?php

namespace homeserver\apiv1\Model;

use homeserver\apiv1\Utilities\Convert;

class Test extends BaseModel
{

    /**
     * @var int $number
     */
    private $number;

    /**
     * @var \DateTime $time
     */
    private $time;

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $num
     */
    public function setNumber($num)
    {
        $this->number = $num;
    }

    /**
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param \DateTime $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }


    public function jsonSerialize()
    {
        $baseArray = parent::jsonSerialize();
        $baseArray['number'] = $this->number;
        $baseArray['time'] = Convert::formatTime($this->time);
        return $baseArray;
    }
}
