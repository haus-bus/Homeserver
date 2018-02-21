<?php

namespace homeserver\apiv1\Controller;


use homeserver\apiv1\Model\BaseModel;

Interface IApiController
{
    /**
     * Retrieves and returns the requested objects
     * @param array $path the path of the requested objects
     * @param array $params the parameters of the api request
     * @return BaseModel[]|array if the api request is a query, the resulting objects will be returned.
     * If the request is a method call, an array that contains the object, on which the method
     * was executed and the methodResult will be returned
     */
    public function processGet(array $path, array $params = array());

    /**
     * Creates the objects of the type in $path
     * @param array $path the path of the objects to create
     * @param string $body the request body that holds the json objects to create
     * @return BaseModel[] the created objects
     */
    public function processPost(array $path, $body);

    /**
     * Updates the objects
     * @param array $path the class of the object which will be updated
     * @param string $body the request body that holds the json objects to update
     * @param array $params the parameter values of the request
     * @return BaseModel[] the updated objects
     */
    public function processPut(array $path, $body, array $params = array());

    /**
     * Deletes the described objects
     * @param array $path the path to the objects which will be deleted
     * @param array $params additional parameter values like a query to determine the objects which will be deleted
     * @return BaseModel[] the deleted objects
     */
    public function processDelete(array $path, array $params = array());

}
