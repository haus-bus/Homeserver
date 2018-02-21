<?php

use function DI\get;
use function DI\object;
use homeserver\apiv1\Utilities\DbAccess;
use Interop\Container\ContainerInterface;

return [
    // database access
    \homeserver\apiv1\Utilities\DbAccess::class =>
        object(DbAccess::class)
            ->constructor(get('db.host'), get('db.user'),
                get('db.name'), get('db.password'), get('db.schema')),

    // message counter
    \homeserver\apiv1\Utilities\IMessageCounter::class => object(\homeserver\apiv1\Utilities\MessageCounter::class),

    // API controller
    \homeserver\apiv1\Controller\IApiController::class => function (ContainerInterface $c) {
        return new \homeserver\apiv1\Controller\ApiController($c);
    },

    // default methodController
    \homeserver\apiv1\Controller\IMethodController::class => function (ContainerInterface $c) {
        return new \homeserver\apiv1\Controller\ApiController($c);
    },

    // communicator
    \homeserver\apiv1\Utilities\ICommunicator::class =>
        object(\homeserver\apiv1\Utilities\Communicator::class)
            ->constructor(get('network.udpPort'), get('network.myObjectId'),
                get('network.udpHeaderBytes'), get(\homeserver\apiv1\Utilities\IMessageCounter::class)),


    // database table names
    'tablenames.basicConfig' => 'basicconfig',
    'tablenames.feature' => 'featureinstances',
    'tablenames.featureClass' => 'featureclasses',
    'tablenames.featureFunction' => 'featurefunctions',
    'tablenames.featureFunctionBitmasks' => 'featurefunctionbitmasks',
    'tablenames.featureFunctionEnums' => 'featurefunctionenums',
    'tablenames.featureFunctionParams' => 'featurefunctionparams',
    'tablenames.graph' => 'graphs',
    'tablenames.graphData' => 'graphdata',
    'tablenames.graphSignal' => 'graphsignals',
    'tablenames.graphSignalEvent' => 'graphsignalevents',
    'tablenames.lastReceived' => 'lastreceived',
    'tablenames.room' => 'rooms',
    'tablenames.roomFeature' => 'roomfeatures',
    'tablenames.test' => 'zz_apitest',
    'tablenames.udpHelper' => 'udphelper',

    // database information
    'dbInfo' => require 'config.dbInfo.php',

    // network
    'network.udpPort' => 9,
    'network.myObjectId' => (9999<<16)+1,
    'network.udpHeaderBytes' => array(0xef,0xef),

    // function types
    'functionTypes' => array('FUNCTION', 'EVENT', 'RESULT', 'ACTION'),
];
