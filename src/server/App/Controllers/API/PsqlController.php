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



/**
 * Class PsqlController
 *
 * An controller for interacting with the Plugin's log files.
 *
 * @package App\Controllers
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 * @final
 */
final class PsqlController
{

    /**
     * @return array
     * @throws DatabaseConnectionException
     */
    private static function getTables() : array
    {
        /** @noinspection PhpUndefinedClassConstantInspection */
        Database::connect(
            getenv("POSTGRES_HOST") ?: Settings::UCRM_DB_HOST,
            (int)getenv("POSTGRES_PORT") ?: Settings::UCRM_DB_PORT,
            getenv("POSTGRES_DB") ?: Settings::UCRM_DB_NAME,
            getenv("POSTGRES_USER") ?: Settings::UCRM_DB_USER,
            getenv("POSTGRES_PASSWORD") ?: Settings::UCRM_DB_PASSWORD
        );

        /** @noinspection SqlResolve */
        $results = Database::query(
            "
            SELECT * FROM pg_catalog.pg_tables
            WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'
            ;
            "
        );

        $results = array_map(
            function($row)
            {
                /** @noinspection SpellCheckingInspection */
                return $row["tablename"];
            },
            $results
        );

        sort($results);

        return $results;
    }

    /**
     * @param string $table
     * @param bool $meta
     *
     * @return array
     * @throws DatabaseConnectionException
     */
    private static function getColumns(string $table, bool $meta = false) : array
    {
        /** @noinspection PhpUndefinedClassConstantInspection */
        Database::connect(
            getenv("POSTGRES_HOST") ?: Settings::UCRM_DB_HOST,
            (int)getenv("POSTGRES_PORT") ?: Settings::UCRM_DB_PORT,
            getenv("POSTGRES_DB") ?: Settings::UCRM_DB_NAME,
            getenv("POSTGRES_USER") ?: Settings::UCRM_DB_USER,
            getenv("POSTGRES_PASSWORD") ?: Settings::UCRM_DB_PASSWORD
        );

        /** @noinspection SqlResolve */
        $results = Database::query(
            "
            SELECT * FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = '$table'
            ;
            "
        );


        $columns = array_map(
            function($row) use ($meta)
            {
                if(!$meta)
                    return $row["column_name"];

                switch($row["udt_name"])
                {
                    case "uuid":        $type = "uuid";         break;
                    case "bool":        $type = "bool";         break;
                    case "int2":        $type = "short";        break;
                    case "int4":        $type = "int";          break;
                    case "int8":        $type = "long";         break;
                    case "float4":      $type = "float";        break;
                    case "float8":      $type = "double";       break;
                    case "text":
                    case "varchar":     $type = "string";       break;
                    case "timestamp":
                    case "timestamptz": $type = "timestamp";    break;
                    case "json":        $type = "json";         break;
                    case "date":        $type = "date";         break;

                    default:            $type = "?"; //$row["udt_name"];
                }

                return [
                    "name" => $row["column_name"],
                    "nullable" => ($row["is_nullable"] === "YES"),
                    "type" => $type,
                ];
            },
            $results
        );

        return $columns;
    }



    /**
     * PsqlController constructor.
     *
     * @param App $app The Slim Application for which to configure routing.
     */
    public function __construct(App $app)
    {
        // Get a local reference to the Slim Application's DI Container.
        $container = $app->getContainer();






        #region /psql/tables/[{table}]

        // Handle GET queries to the file logs...
        $app->get(
            "/psql/tables/[{table}]",

            function (Request $request, Response $response, array $args) use ($container)
            {

                if (!array_key_exists("table", $args) || !$args["table"])
                    return $response->withJson(self::getTables());

                return $response->withJson(self::getColumns($args["table"], true));
            }
        );

        // Handle GET queries to the file logs...
        $app->get(
            "/psql/tables",

            function (Request $request, Response $response, array $args) use ($container)
            {
                return $response->withJson(self::getTables());
            }
        );

        $app->get(
            "/psql/schemas",

            function (Request $request, Response $response, array $args) use ($container)
            {
                $results = Database::query(
                    "
                    SELECT table_name, column_name, udt_name, is_nullable::bool, ordinal_position
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE table_name IN (
                        SELECT tablename
                        FROM pg_catalog.pg_tables
                        WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'
                    )
                    ;
                    "
                );

                array_multisort(
                    array_column($results, "table_name"),  SORT_ASC,
                    //array_column($results, "column_name"), SORT_ASC,
                    array_column($results, "ordinal_position"), SORT_ASC,
                    $results
                );

                return $response->withJson($results);



            }
        );

        $app->post(
            "/psql/query",

            function (Request $request, Response $response, array $args) use ($container)
            {
                $body = $request->getBody()->getContents();

                // NOTE: Here we will need to "sanitize" the request to prevent writeable queries!!!

                /** @noinspection PhpUndefinedClassConstantInspection */
                Database::connect(
                    getenv("POSTGRES_HOST") ?: Settings::UCRM_DB_HOST,
                    (int)getenv("POSTGRES_PORT") ?: Settings::UCRM_DB_PORT,
                    getenv("POSTGRES_DB") ?: Settings::UCRM_DB_NAME,
                    getenv("POSTGRES_USER") ?: Settings::UCRM_DB_USER,
                    getenv("POSTGRES_PASSWORD") ?: Settings::UCRM_DB_PASSWORD
                );

                $results = Database::query($body);

                return $response->withJson($results);



            }
        );



        // Handle GET queries to the file logs...
        $app->post(
            "/psql/format",

            function (Request $request, Response $response, array $args) use ($container)
            {
                $body = $request->getBody()->getContents();

                $formatted = SqlFormatter::format($body);

                return $response->withHeader("Content-Type", "text/html")->write($formatted);
            }
        );




        $app->get(
            "/psql/user-groups",

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



    }

}
