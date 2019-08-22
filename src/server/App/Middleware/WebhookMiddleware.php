<?php
declare(strict_types=1);

namespace App\Middleware;

use Exception;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use UCRM\REST\Endpoints\WebhookEvent;

/**
 * A Slim Framework Middleware Module for handling UCRM Webhook Events.
 *
 * @package App\Middleware
 * @author Ryan Spaeth <rspaeth@mvqn.net
 * @copyright 2019 Spaeth Technologies, Inc.
 */
class WebhookMiddleware
{
    /**
     * @var App|null The Slim Framework Application.
     */
    private $app = null;

    /**
     * Middleware Constructor.
     *
     * @param App $app The Slim Framework Application to which this Middleware belongs.
     */
    public function __construct(App $app)
    {
        // Assign local variables to instance properties, as needed...
        $this->app = $app;
    }

    /**
     * Middleware Invocation method.
     *
     * @param Request $request      The current Request object.
     * @param Response $response    The current Response object.
     * @param Callable $next        The next middleware callable.
     *
     * @return Response             Returns a Response object.
     * @throws Exception            Throws an Exception if the RestClient cannot communicate with the REST API.
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        // IF the current request method is "POST" AND the request is coming from "localhost"...
        if($request->getMethod() === "POST" && $request->getUri()->getHost() === "localhost")
        {
            // ...THEN get the request body JSON object as an associative array.
            $body = $request->getParsedBody();

            // IF the body contains ALL of the necessary fields...
            if (array_key_exists("uuid",        $body) &&
                array_key_exists("changeType",  $body) &&
                array_key_exists("entity",      $body) &&
                array_key_exists("entityId",    $body) &&
                array_key_exists("eventName",   $body) &&
                array_key_exists("extraData",   $body))
            {
                // ...THEN extract the parts into individual variables for use.
                list($uuid, $changeType, $entity, $entityId, $eventName, $extraData) = array_values($body);

                // Attempt to validate the current Webhook Event against the REST API.
                $results = WebhookEvent::getByUuid($uuid);

                // IF the resulting UUID is the same as that of the request body...
                if($results->getUuid() === $uuid)
                {
                    // ...THEN we have a valid Webhook Event, so check for the appropriate handler class.
                    $class = "App\\Handlers\\Webhooks\\". ucfirst($entity) . "Handler";

                    // IF the Handler class exists...
                    if(class_exists($class))
                    {
                        // ...THEN instantiate it...
                        $object = new $class();

                        // ...AND IF a method matching the current Webhooks Event's "changeType" exists...
                        if(method_exists($object, $changeType))
                        {
                            // Pass execution to correct method in the Handler class!
                            return $object->$changeType($request, $response, $next);
                        }

                    }

                    // OTHERWISE, no Handler exists, so return HTTP 501 and terminate execution!
                    http_response_code(501);
                    die("A webhook handler for '$entity.$changeType' could not be found!");

                    // NOTE: The plugin developer is responsible for creating the Handlers for supported event types!
                }

                // OTHERWISE, the Webhook Event is invalid, so return HTTP 400 and terminate execution!
                http_response_code(400);
                die("The UUID of the received webhook event is invalid!");

            }

        }

        // OTHERWISE, the request is not handled by this Middleware, so continue!
        return $next($request, $response);
    }

}
