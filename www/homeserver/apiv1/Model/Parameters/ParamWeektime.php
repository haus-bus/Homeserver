<?php

namespace homeserver\apiv1\Model\Parameters;



class ParamWeektime extends FeatureFunctionParam
{

    /**
     * the type of this parameter, i.e. BYTE, BITMASK, WORD, ...
     */
    const TYPE = "WEEKTIME";


    /**
     * @param mixed $paramValue the value to convert to bytes
     * @return array returns an array containing the given value as bytes
     * @throws \Exception if the value doesn't fit for this parameter type
     */
    public function getBytes($paramValue)
    {
        // todo: implement
        throw new \Exception("'$paramValue' is not a valid value for " . $this::TYPE);
    }

}
