<?php

namespace homeserver\apiv1\Model\Parameters;



class ParamByte extends FeatureFunctionParam
{

    /**
     * the type of this parameter, i.e. BYTE, BITMASK, WORD, ...
     */
    const TYPE = "BYTE";


    /**
     * @param mixed $paramValue the value to convert to bytes
     * @return array returns an array containing the given value as bytes
     * @throws \Exception if the value doesn't fit for this parameter type
     */
    public function getBytes($paramValue)
    {
        if ($paramValue == null) return array(0);
        elseif (is_numeric($paramValue) && $paramValue >= 0 && $paramValue < 256) return array($paramValue);
        else throw new \Exception("'$paramValue' is not a valid value for " . $this::TYPE);
    }

}
