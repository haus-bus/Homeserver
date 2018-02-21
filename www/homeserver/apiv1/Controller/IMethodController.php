<?php

namespace homeserver\apiv1\Controller;


interface IMethodController
{

    /**
     * @param \homeserver\apiv1\Model\BaseModel $model the model on which the method will be executed
     * @param string $method the name of the method to execute
     * @param array $params the method parameters in a key => value array where the key names match the parameter names of the executed method
     * @return mixed the method result
     */
    function executeMethod($model, $method, array $params = array());
}
