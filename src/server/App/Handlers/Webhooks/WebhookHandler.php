<?php
declare(strict_types=1);

namespace App\Handlers\Webhooks;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * An example WebhookHandler that also handles the "webhook.test" webhook event.
 *
 * @package App\Handlers\Webhooks
 * @author Ryan Spaeth <rspaeth@mvqn.net
 * @copyright 2019 Spaeth Technologies, Inc.
 */
final class WebhookHandler
{
    /**
     * Handles the "webhook.test" webhook event.
     *
     * @param Request $request      The current Request object.
     * @param Response $response    The current Response object.
     * @param Callable $next        The next middleware callable.
     *
     * @return Response             Returns a Response object.
     */
    public function test(Request $request, Response $response, callable $next)
    {
        // NOTE: Perform any code execution here, but be sure to do one of the following:
        // - Return a Response object, signifying that this Middleware has performed all required processing.
        // - Return the $next callable, to allow any additional Middleware processing.
        // - Terminate execution if no further Slim Framework handling is necessary.

        // Here we simply return a JSON object signifying the test was successful!
        return $response->withJson([ "test" => "successful" ]);
    }

    // NOTE: In other handlers, a function for each supported eventName will need to be declared.

}
