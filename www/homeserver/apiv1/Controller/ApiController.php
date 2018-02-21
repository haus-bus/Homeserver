<?php

namespace homeserver\apiv1\Controller;

use Exception;
use homeserver\apiv1\Model\BaseModel;
use homeserver\apiv1\Repository\BaseRepository;

class ApiController implements IMethodController, IApiController
{

    /**
     * @var \Interop\Container\ContainerInterface $container
     */
    private $container;

    /**
     * ApiController constructor.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(\Interop\Container\ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Retrieves and returns the requested objects
     *
     * @param array $path the path of the requested objects
     * @param array $params the parameters of the api request
     * @return BaseModel[]|array if the api request is a query, the resulting objects will be returned.
     * If the request is a method call, an array that contains the object, on which the method
     * was executed and the methodResult will be returned
     * @throws \Exception if something goes wrong
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function processGet(array $path, array $params = array())
    {
        // checks
        if ($path == null || count($path) <= 0) {
            throw new Exception("no path defined");
        }
        if (count($path) > 3) {
            throw new \Exception("the depth of the path is limited to 3: class/id/methodCall");
        }

        // process array parameters
        $this->processParams($params);
        $this->processParams($path);

        // determine repository
        $repo = $this->getRepoByClass($path[0]);

        // select action dependant on count of parameters in path
        if (count($path) == 1) {
            return $repo->query($params);
        } else {
            // get specific object
            $ids = is_array($path[1]) ? $path[1] : array($path[1]);
            $objects = $repo->getByIds($ids);

            if (count($path) == 2) {
                // return the object
                return $objects;
            } elseif (count($path) == 3) {
                // check model
                if ($objects == null || count($objects) == 0) {
                    throw new Exception("no object of the type '$path[0]' with ID '" . implode(",",
                            $ids) . "' was found");
                }

                // retrieve methodController
                $rc = new \ReflectionClass($objects[0]);
                $methodControllerName = __NAMESPACE__ . "\\" . $rc->getShortName() . "MethodController";
                if ($this->container->has($methodControllerName)) {
                    $methodController = $this->container->get($methodControllerName);
                } else {
                    $methodController = $this->container->get(IMethodController::class);
                }

                // execute method on object
                foreach ($objects as $object) {
                    $result[] = $methodController->executeMethod($object, $path[2], $params);
                }
                return $result;
            } else {
                throw new \Exception("the depth of the path is limited to 3: class/objectId/methodCall");
            }
        }
    }

    /**
     * Updates the objects
     *
     * @param array $path the class of the object which will be updated
     * @param string $body the request body that holds the json objects to update
     * @param array $params the parameter values of the request
     * @return BaseModel[] the updated objects
     * @throws Exception if something goes wrong
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function processPut(array $path, $body, array $params = array())
    {
        // checks
        if ($path == null || count($path) != 1) {
            throw new Exception(
                "invalid path: the path must contain only the class of the object which will be updated");
        }

        // process parameters
        $this->processParams($params);
        $propertyNames = $params['properties'];
        $objects = json_decode($body);

        // determine repository
        $repo = $this->getRepoByClass($path[0]);

        return $repo->update($objects, $propertyNames);
    }

    /**
     * Creates the objects of the type in $path
     *
     * @param array $path the path of the objects to create
     * @param string $body the request body that holds the json objects to create
     * @return BaseModel[] the created objects
     * @throws Exception if something goes wrong
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function processPost(array $path, $body)
    {
        // checks
        if ($path == null || count($path) != 1) {
            throw new Exception(
                "invalid path: the path must contain only the class of the object which will be created");
        }
        $objects = json_decode($body);

        // determine repository
        $repo = $this->getRepoByClass($path[0]);

        return $repo->add($objects);
    }

    /**
     * Deletes the described objects
     *
     * @param array $path the path to the objects which will be deleted
     * @param array $params additional parameter values like a query to determine the objects which will be deleted
     * @return BaseModel[] the deleted objects
     * @throws Exception if something goes wrong
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function processDelete(array $path, array $params = array())
    {
        // checks
        if ($path == null || count($path) <= 0 || count($path) > 2) {
            throw new Exception(
                "invalid path: the path must consist of the class and optional the ids of the objects which will be deleted");
        }

        // determine repository
        $repo = $this->getRepoByClass($path[0]);

        // process array parameters
        $this->processParams($params);
        $this->processParams($path);

        switch (count($path)) {
            case 1:
                return $repo->deleteByQuery($params);

            case 2:
                $ids = is_array($path[1]) ? $path[1] : array($path[1]);
                return $repo->deleteByIds($ids);

            default:
                throw new Exception("invalid path");
        }
    }

    /**
     * @param \homeserver\apiv1\Model\BaseModel $model the model on which the method will be executed
     * @param string $method the name of the method to execute
     * @param array $params the method parameters in a key => value array where the key names match the parameter names of the executed method
     * @return array an array that contains the object, on which the method was executed and the methodResult
     * @throws Exception
     */
    function executeMethod($model, $method, array $params = array())
    {
        // check if method exists
        if (!method_exists($model, $method)) {
            throw new \Exception(get_class($model) . " does not provide the method '$method'");
        }

        // get parameter names
        $f = new \ReflectionMethod(get_class($model), $method);
        $methodParams = array();
        foreach ($f->getParameters() as $p) {
            $methodParams[] = key_exists($p->name, $params) ? $params[$p->name] : null;
        }

        // pass parameters to method and execute method. maybe there is a better way??
        switch (count($methodParams)) {
            case 0:
                $result = $model->$method();
                break;

            case 1:
                $result = $model->$method($methodParams[0]);
                break;

            case 2:
                $result = $model->$method($methodParams[0], $methodParams[1]);
                break;

            case 3:
                $result = $model->$method($methodParams[0], $methodParams[1], $methodParams[2]);
                break;

            case 4:
                $result = $model->$method($methodParams[0], $methodParams[1], $methodParams[2], $methodParams[3]);
                break;

            case 5:
                $result = $model->$method($methodParams[0], $methodParams[1], $methodParams[2], $methodParams[3],
                    $methodParams[4]);
                break;

            case 6:
                $result = $model->$method($methodParams[0], $methodParams[1], $methodParams[2], $methodParams[3],
                    $methodParams[4], $methodParams[5]);
                break;

            case 7:
                $result = $model->$method($methodParams[0], $methodParams[1], $methodParams[2], $methodParams[3],
                    $methodParams[4], $methodParams[5], $methodParams[6]);
                break;

            case 8:
                $result = $model->$method($methodParams[0], $methodParams[1], $methodParams[2], $methodParams[3],
                    $methodParams[4], $methodParams[5], $methodParams[6], $methodParams[7]);
                break;

            case 9:
                $result = $model->$method($methodParams[0], $methodParams[1], $methodParams[2], $methodParams[3],
                    $methodParams[4], $methodParams[5], $methodParams[6], $methodParams[7], $methodParams[8]);
                break;

            case 10:
                $result = $model->$method($methodParams[0], $methodParams[1], $methodParams[2], $methodParams[3],
                    $methodParams[4], $methodParams[5], $methodParams[6], $methodParams[7], $methodParams[8],
                    $methodParams[9]);
                break;

            default:
                throw new Exception("more than 10 parameters? really? add this case to the implementation or show a better way ;-)");
        }
        return array('object' => $model, 'methodResult' => $result);
    }

    /**
     * Processes and converts the values. For example json encoded strings will be decoded to arrays
     *
     * @param array $params the parameters to convert
     */
    private function processParams(array &$params)
    {
        foreach ($params as $key => $param) {
            $lastIdx = strlen($param) - 1;
            if ((strpos($param, '{') == 0 && strpos($param, '}', $lastIdx) == $lastIdx)
                || (strpos($param, '[') == 0 && strpos($param, ']', $lastIdx) == $lastIdx)) {
                // parameter is json encoded
                $params[$key] = json_decode($param, true);
            }
        }
    }

    /**
     * Gets the repository for the specified class name
     *
     * @param string $className the name of the class for which the repository is needed
     * @return BaseRepository the repository for the class
     * @throws Exception if the repository could not be determined
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getRepoByClass($className)
    {
        $repoName = 'homeserver\\apiv1\\Repository\\' . ucfirst($className) . 'Repository';
        if (!$this->container->has($repoName)) {
            throw new Exception("no repository for '$className' could be found");
        }
        return $this->container->get($repoName);
    }
}
