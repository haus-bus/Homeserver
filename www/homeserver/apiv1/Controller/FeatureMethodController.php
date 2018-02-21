<?php

namespace homeserver\apiv1\Controller;

use DI\Annotation\Inject;

class FeatureMethodController implements IMethodController
{

    /**
     * @Inject
     * @var IMethodController $defaultMethodController
     */
    private $defaultMethodController;


    /**
     * @param \homeserver\apiv1\Model\BaseModel $model the model on which the method will be executed
     * @param string $method the name of the method to execute
     * @param array $params the method parameters in a key => value array where the key names match the parameter names of the executed method
     * @return mixed the method result
     * @throws \Exception
     */
    function executeMethod($model, $method, array $params = array())
    {
        // check if method exists and call execInstanceMethod if not
        if (!method_exists($model, $method)) {
            $params["function"] = $method;
            $method = "execInstanceMethod";

            // check if 'params' is defined, if not: create the array
            if (!array_key_exists('params', $params)) {
                foreach ($params as $key => $par) {
                    $params['params'][$key] = $par;
                }
            }
        }

        // and now default again
        return $this->defaultMethodController->executeMethod($model, $method, $params);
    }
}
