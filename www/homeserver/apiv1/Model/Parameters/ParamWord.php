<?php

namespace homeserver\apiv1\Model\Parameters;



class ParamWord extends FeatureFunctionParam
{

    /**
     * the type of this parameter, i.e. BYTE, BITMASK, WORD, ...
     */
    const TYPE = "WORD";


    /**
     * @param mixed $paramValue the value to convert to bytes
     * @return array returns an array containing the given value as bytes
     * @throws \Exception if the value doesn't fit for this parameter type
     */
    public function getBytes($paramValue)
    {
        if ($paramValue == null) $paramValue = 0;
        elseif (! is_numeric($paramValue))
            throw new \Exception("'$paramValue' is not a valid value for " . $this::TYPE);

        $result[0] = $paramValue & 0xff;
        $result[1] = ($paramValue >> 8) & 0xff;
        return $result;
    }
}
