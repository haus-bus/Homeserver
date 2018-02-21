<?php

namespace homeserver\apiv1\Model\Parameters;



class ParamBlob extends FeatureFunctionParam
{

    /**
     * the type of this parameter, i.e. BYTE, BITMASK, WORD, ...
     */
    const TYPE = "BLOB";


    /**
     * @param mixed $paramValue the value to convert to bytes
     * @return array returns an array containing the given value as bytes
     * @throws \Exception if the value doesn't fit for this parameter type
     */
    public function getBytes($paramValue)
    {
        if ($paramValue == null) return array(0);
        elseif (! is_string($paramValue))
            throw new \Exception("'$paramValue' is not a valid value for " . $this::TYPE);

        $i = 0;
        for (; $i < strlen($paramValue); $i++)
        {
            $result[$i] = ord(substr($paramValue, $i, 1));
        }
        $result[$i] = 0;
        return $result;
    }

}
