<?php

namespace homeserver\apiv1\Model\Parameters;



class ParamEnum extends FeatureFunctionParam
{

    /**
     * the type of this parameter, i.e. BYTE, BITMASK, WORD, ...
     */
    const TYPE = "ENUM";


    /**
     * @param mixed $paramValue the value to convert to bytes
     * @return array returns an array containing the given value as bytes
     * @throws \Exception if the value doesn't fit for this parameter type
     */
    public function getBytes($paramValue)
    {
        if ($paramValue == null) $paramValue = 0;
        $possibleEnums = $this->getEnums();

        // numeric value given?
        if (is_numeric($paramValue) && count(array_filter($possibleEnums,
                function ($val) use ($paramValue) {return $val->getValue() == $paramValue;})) == 1) return array($paramValue);

        // string value given?
        else {
            $enum = array_filter($possibleEnums, function ($val) use ($paramValue) {
                return $val->getName() == $paramValue;
            });
            if (count($enum) == 1) return array(array_pop($enum)->getValue());
        }

        throw new \Exception("'$paramValue' is not a valid value for " . $this::TYPE);
    }

}
