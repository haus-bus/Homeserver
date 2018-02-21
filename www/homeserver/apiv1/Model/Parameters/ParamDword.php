<?php

namespace homeserver\apiv1\Model\Parameters;



class ParamDword extends FeatureFunctionParam
{

    /**
     * the type of this parameter, i.e. BYTE, BITMASK, WORD, ...
     */
    const TYPE = "DWORD";


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

        $in = (float) $paramValue;
        $result[0] = $in & 0xff;
        $result[1] = ($in >> 8) & 0xff;
        $result[2] = ($in >> 16) & 0xff;
        $result[3] = ($in >> 24) & 0xff;
        return $result;
    }

}
