<?php
declare(strict_types=1);

namespace App\Controllers;

use Monolog\Logger;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use UCRM\Common\Log;

/**
 * Class ApiController
 *
 * An API controller.
 *
 * @package App\Controllers
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 * @final
 */
final class ApiController
{
    /**
     * ApiController constructor.
     *
     * @param App $app The Slim Application for which to configure routing.
     */
    public function __construct(App $app)
    {

        $app->group("/api", function() use ($app) {

            // Get a local reference to the Slim Application's DI Container.
            $container = $app->getContainer();

            // Include the LogsController for querying the Plugin's log files.
            new API\LogsController($app);

            new API\PsqlController($app);

            new API\PermissionsController($app);

            //
            // NOTE: Include any additional common API Controllers here...
            //

            // Handle the root "/api[/]" functionality here...
            $app->get("[/]",

                function (Request $request, Response $response, array $args) use ($container)
                {


                    // Return the API information as JSON!
                    return $response->withJson(
                        [
                            "version" => "1.0",
                            "endpoints" => [
                                "/api/logs" => [
                                    "name" => "Logs",
                                    "description" => "An endpoint for querying the Plugin's log files."
                                ]
                            ]
                        ],
                        200,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                }
            );

            $app->get("*",

                function (Request $request, Response $response, array $args) use ($container)
                {

                    // Return the API information as JSON!
                    return $response->withJson(
                        [
                            "version" => "1.0",
                            "endpoints" => [
                                "/api/logs" => [
                                    "name" => "Logs",
                                    "description" => "An endpoint for querying the Plugin's log files."
                                ]
                            ]
                        ],
                        200,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                }
            );

        })->add(function (Request $request, Response $response, $next) use ($app) {
            Log::debug($request->getUri()->getPath(), Log::REST);
            return $next($request, $response);
        });

    }

}
