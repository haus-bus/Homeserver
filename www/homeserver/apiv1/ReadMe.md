# API v1

## General

The API provides access to the haus-bus components. It is possible to access and to modify the data in the database like adding or editing rooms and the features belonging to them via http. It is also possible to send commands to specific features like turning switches on or set the brightness of a light.

The API is based on JSON and supports the typical CRUD (create, read, update, delete) operations and method calls on objects.

## Homeserver requirements

1. Enable mod_rewrite (used to make the urls more human readable)  
`sudo a2enmod rewrite`  
`sudo service apache2 restart`
2. Install php-apc (the cache used for the dependency injection)  
`sudo apt-get install php-apc`  
`sudo systemctl daemon-reload`  
`sudo service apache2 restart`

### For developers only

1. The api uses some external packages which must be installed via [composer](https://getcomposer.org/). Install composer on your developer machine and run `composer install` with the api-directory as the current working directory
2. You find the docs (generated from the phpdoc annotations) [here](https://dev.wv-pankraz.de/dev/hausbus/apiv1/docs)

## Usage

The entrypoint for every request is `/apiv1/index.php` and the only required parameter is `_path` which defines, which resource will be selected for the request. The .htaccess-file contains a rewrite rule so the request

`/apiv1/resource/`

will be automatically rewritten to

`/apiv1/index.php?_path=resource`.

### Read objects

**_Request method to use: 'GET'_**

There are different ways to get objects via the API:

#### Get multiple objects by class

To retrieve all objects by class just call the api with the class name as _path:

`/apiv1/room` will return all rooms in JSON format.

To reduce the number of objects, you can pass query parameters as further arguments:

`/apiv1/room?name=like EG %&id=[">= 5", "< 11"]`

This request will return all rooms which names start with 'EG ' and the id is between 5 and including 10.

#### Get a single or multiple objects by ID

To retrieve a single object append the id to the _path:

`/apiv1/room/123`

This request returns an array containing the room with id 123 or an empty array, if the id doesn't exist.

To retrieve multiple objects by multiple ids, append the ids as an array to the _path:

`/apiv1/room/[1,2,3]`

This request will return the rooms with the ids 1, 2 and 3.

### Create new objects

**_Request method to use: 'POST'_**

To create new objects, just send a POST-request to the API. The only required parameter is the class name of the object in the `_path`. The objects to create must be passed as json encoded objects in the request body:

`apiv1/room` \
`request body: [{"name": "my new room"}, {"name": "the 2nd new room"}]`

This request will create 2 new room objects and return them in JSON format.

### Update existing objects

**_Request method to use: 'PUT'_**

To update some objects, just send a PUT-request to the API. It will update the objects and return the updated objects in JSON format. The only required parameter is the class name of the object in the `_path`. The objects to update must be passed as JSON encoded objects in the request body:

`apiv1/room`  
`request body: [{"id": 1, "name": "new room name"}, {"id": 23, "name": "2nd new room name"}]`

The passed objects must contain at least the id and the properties to update.

You can also send the whole object and update only a set of properties when you pass the property names as a parameter:

`apiv1/room?properties=["name", "description"]`  
`request body: the whole objects with all other properties`

### Delete objects

**_Request method to use: 'DELETE'_**

There are several ways to delete objects via the API. Every form of request returns the deleted objects.

#### Delete by IDs

To delete objects by one or multiple IDs, just send a DELETE request to the API. Required parameters are the class name of the object(s) and the ID(s) in the `_path`:

`apiv1/room/3`

or for multiple IDs:

`apiv1/room/[3, 6, 8]`

#### Delete by query

To delete objects by constraints send a DELETE request without IDs but with named parameters to match the objects:

`/apiv1/room?name=like EG %&id=[">= 5", "< 11"]`

This request will delete all rooms which names start with 'EG ' and the id is between 5 and including 10. A delete request without constraints is for safety reasons not allowed. If you really want to delete all objects, specify a constraint that will match all objects.

### Call methods on objects

**_Request method to use: 'GET'_**

An object method can also be called via the API. Just retrieve the object(s) like shown above and append the method name to call to the _path:

`/apiv1/room/[123,456]/getFeatures`

will load the rooms with the ids 123 and 456 and call 'getFeatures' on each of the objects. The result is an array containing the objects itself and the method call results of each object.

Every method implemented in the corresponding class can be called via the API, see the docs for further information.

#### Call methods on features

A special case is the method call on features. There are no implemented methods like 'on' for switches or 'setBrightness' for dimmers. Instead of these special methods, which aren't implemented for each featureClass, there is a method called 'execInstanceMethod' with the parameters 'function' and 'params'. Calling this method allows you to send a command to a haus-bus component like a switch or a dimmer.

So let's start with an example and set the brightness of the dimmer with id 5 to 50% for 10 seconds:

`apiv1/feature/5/execInstanceMethod?function=setBrightness&params={"brightness":50, "duration":10}`

That works, but is not really nice to write. So there is a special methodController implemented, that maps the parameters of a method call on a feature the following way:

`apiv1/feature/5/setBrightness?brightness=50&duration=10`

That's shorter and easier to understand. So if a method is not implemented in the feature, the API will call 'execInstanceMethod' and pass the given method name as the 'function' parameter and all following parameters as an array to 'params'.
