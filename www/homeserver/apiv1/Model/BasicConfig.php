<?php

namespace homeserver\apiv1\Model;


class BasicConfig extends BaseModel
{
    /**
     * @var mixed $value the value
     */
    private $value;


    /**
     * @return mixed the value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value the value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }


    public function jsonSerialize()
    {
        $baseArray = parent::jsonSerialize();
        $baseArray['value'] = $this->value;
        return $baseArray;
    }
}
