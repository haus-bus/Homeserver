<?php

namespace homeserver\apiv1\Model\Parameters;




class ParamWordlist extends FeatureFunctionParam
{

    /**
     * the type of this parameter, i.e. BYTE, BITMASK, WORD, ...
     */
    const TYPE = "WORDLIST";


    /**
     * @param mixed $paramValue the value to convert to bytes
     * @return array returns an array containing the given value as bytes
     * @throws \Exception if the value doesn't fit for this parameter type
     */
    public function getBytes($paramValue)
    {
        $result = array();
        if ($paramValue == null || $paramValue == "") return $result;
        elseif (strlen($paramValue) > 0 && strpos($paramValue, ",") === false)
            throw new \Exception("'$paramValue' is not a valid value for " . $this::TYPE);

        $i = 0;
        $parts = explode(";", $paramValue);
        foreach ($parts as $value)
        {
            $elements = explode(",", $value);
            if (count($elements) != 2
                || !is_numeric($elements[0]) || $elements[0] > 255
                || !is_numeric($elements[1]) || $elements[1] > 255)
                throw new \Exception("'$paramValue' is not a valid value for " . $this::TYPE);
            $result[$i++] = $elements[0];
            $result[$i++] = $elements[1];
        }
        return $result;
    }

}
