<?php /** @noinspection PhpUnusedLocalVariableInspection PhpUnusedParameterInspection */
declare(strict_types=1);
namespace App\Controllers\API;

use App\Formatters\SqlFormatter;
use App\Settings;
use MVQN\Data\Database;
use MVQN\Data\Exceptions\DatabaseConnectionException;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use UCRM\Common\Plugin;


/**
 * Class PermissionsController
 *
 * An controller for interacting with the Plugin's log files.
 *
 * @package App\Controllers
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 * @final
 */
final class PermissionsController
{
    public const FILE_PATH = __DIR__ . "/../../../../data/permissions.json";

    public const JSON_OPTS = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    /**
     * PsqlController constructor.
     *
     * @param App $app The Slim Application for which to configure routing.
     */
    public function __construct(App $app)
    {
        // Get a local reference to the Slim Application's DI Container.
        $container = $app->getContainer();

        $dir = realpath(dirname(self::FILE_PATH));

        if($dir && !file_exists($dir . "/permissions.json"))
        {
            $data = [
                "groups" => [
                    "Admin Group"
                ],
                "users" => [],
            ];

            file_put_contents($dir."/permissions.json", json_encode($data, self::JSON_OPTS), LOCK_EX);
        }

        $path = realpath($dir . "/permissions.json");

        $data = json_decode(file_get_contents($path), true);



        $app->get("/permissions/groups[/]",
            function (Request $request, Response $response, array $args) use ($container)
            {
                /** @noinspection PhpUndefinedClassConstantInspection */
                Database::connect(
                    getenv("POSTGRES_HOST") ?: Settings::UCRM_DB_HOST,
                    (int)getenv("POSTGRES_PORT") ?: Settings::UCRM_DB_PORT,
                    getenv("POSTGRES_DB") ?: Settings::UCRM_DB_NAME,
                    getenv("POSTGRES_USER") ?: Settings::UCRM_DB_USER,
                    getenv("POSTGRES_PASSWORD") ?: Settings::UCRM_DB_PASSWORD
                );

                $results = Database::query(
                    "
                    SELECT * FROM user_group;                
                    "
                );

                return $response->withJson($results);
            }
        );

        $app->get("/permissions/groups/allowed",
            function (Request $request, Response $response, array $args) use ($container, $path, $data)
            {
                return $response->withJson($data["groups"]);
            }
        );

        $app->post("/permissions/groups/allowed",
            function (Request $request, Response $response, array $args) use ($container, $path, $data)
            {
                $body = json_decode($request->getBody()->getContents(), true);

                if(array_key_exists("groups", $body) && count($body["groups"]) > 0)
                    $data["groups"] = $body["groups"];

                file_put_contents($path, json_encode($data, self::JSON_OPTS) . "\n", LOCK_EX);

                return $response->withJson($data);
            }
        );






    }

}
