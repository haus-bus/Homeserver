<?php

namespace homeserver\apiv1;

use homeserver\apiv1\Controller\IApiController;

define("PATH_NAME", "_path");
try {
    // initialize container
    $container = require __DIR__ . '/Configuration/container.php';

    // check parameters
    if (key_exists(PATH_NAME, $_REQUEST)) {
        $path = array_filter(explode('/', $_REQUEST[PATH_NAME]), function ($val) {
            return $val != '';
        });
    } else {
        throw new \Exception('no path defined');
    }

    /**
     * @var IApiController $controller
     */
    $controller = $container->get(IApiController::class);

    // process the request depending on the request method
    $method = $_SERVER['REQUEST_METHOD'];
    switch ($method) {
        case "GET":
            $result = $controller->processGet($path,
                array_filter($_GET, function ($key) {
                    return $key != PATH_NAME;
                },
                    ARRAY_FILTER_USE_KEY));
            break;

        case "POST":
            $body = file_get_contents('php://input');
            $result = $controller->processPost($path, $body);
            break;

        case "PUT":
            $body = file_get_contents('php://input');
            $result = $controller->processPut($path, $body,
                array_filter($_GET, function ($key) {
                    return $key != PATH_NAME;
                },
                    ARRAY_FILTER_USE_KEY));
            break;

        case "DELETE":
            $result = $controller->processDelete($path,
                array_filter($_GET, function ($key) {
                    return $key != PATH_NAME;
                },
                    ARRAY_FILTER_USE_KEY));
            break;

        default:
            throw new \Exception("The requestMethod '$method' is undefined in this api");
    }

    // return output
    header('Content-Type: application/json');
    echo json_encode($result);

} catch (\Exception $ex) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(array(
        'message' => $ex->getMessage(),
        'code' => $ex->getCode(),
        'trace' => $ex->getFile() . ':' . $ex->getLine()
    ));
}
