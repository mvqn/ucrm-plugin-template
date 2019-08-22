<?php /** @noinspection PhpUnusedLocalVariableInspection PhpUnusedParameterInspection */
declare(strict_types=1);
namespace App\Controllers\API;

use MVQN\Collections\Collection;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use UCRM\Common\Exceptions\PluginNotInitializedException;
use UCRM\Common\Log;
use UCRM\Common\LogEntry;
use UCRM\Common\Plugin;

use \Exception;
use \DateTimeImmutable;

/**
 * Class LogsController
 *
 * An controller for interacting with the Plugin's log files.
 *
 * @package App\Controllers
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 * @final
 */
final class LogsController
{
    // NOTE: A lot of the below configuration and functions rely on this Plugin using OUR logging system and will have
    // numerous issues if other entries are added to the log files/databases that do not follow our strict log formats.

    private const DEFAULT_ROWS_PER_PAGE = 10;

    /**
     * A helper function to collect all of the log metadata.
     *
     * @return array Returns an array of the discovered log files and databases.
     * @throws PluginNotInitializedException
     */
    private static function getLogsMetadata(): array
    {
        // Initialize an empty array of log metadata.
        $logs = [];

        #region FILE-BASED

        // Start with a list containing the standard plugin file.
        $files = ["../plugin.log"];

        // IF a "logs/" folder exists in the Plugin's "data/" folder, THEN include any files from there as well...
        if(file_exists(Plugin::getLogsPath()))
            $files = array_merge( $files , scandir(Plugin::getLogsPath()));

        // Loop through each of the files in the list...
        foreach(array_filter($files) as $file)
        {
            // IF the file is one of the special files, THEN skip this one!
            if($file === "." || $file === "..")
                continue;

            //
            // NOTE: Perform any other desired checks/restrictions against the file here! Keeping in mind, that the REST
            // endpoint "/api/logs/[file]" will be even more restrictive with the results.
            //

            //if($file === "../plugin.log")
            //    $path = realpath(Plugin::getDataPath()."/plugin.log");
            //else
            // OTHERWISE, get the absolute path of the file.
            $path = realpath(Plugin::getLogsPath()."/$file");

            //var_dump(Plugin::getDataPath()."/logs/$file");
            //exit();

            // IF the file does not exist, THEN skip this one!
            if(!$path)
                continue;

            // IF this file is the default "plugin.log" file, THEN change the name to resemble the others!
            if($file === "../plugin.log")
                 $file = "plugin.log";

            // Get the base file name for use in naming.
            $name = basename($file, ".log");

            // Count the number of lines in this file.
            $rows = count(file($path, FILE_IGNORE_NEW_LINES));

            $standard = true;

            // IF the number of lines is NOT evenly divisible by the size of LogEntry, THEN it is invalid!
            if($rows % 3 !== 0)
                //die("Error parsing log file '$file'!");
                $standard = false;

            // Append the log information to the results...
            $logs[$name] = [
                "type" => $standard ? "txt" : "raw",
                "file" => $file,
                "path" => $path,
                "rows" => $standard ? intval($rows / 3) : intval($rows),
                "totalPages" => intval(ceil(($standard ? $rows / 3 : $rows) / self::DEFAULT_ROWS_PER_PAGE)),
            ];
        }

        #endregion

        #region DATA-BASED

        // Map a list of the distinct "channels" used in the database table.
        $channels = array_map(
            function($element)
            {
                return $element["channel"];
            },
            Plugin::dbQuery("SELECT DISTINCT channel FROM logs;")
        );

        // Loop through each discovered "channel"...
        foreach($channels as $channel)
        {
            // Get the database to keep things uniform in the metadata.
            $dataPath = realpath(Plugin::getDataPath() . "/plugin.db");

            // Count the number of rows in the table for the current channel.
            $count = intval(Plugin::dbQuery("SELECT COUNT(*) FROM logs WHERE channel = '$channel';")[0]["COUNT(*)"]);

            // Append the log information to the results...
            $logs[strtolower($channel)] = [
                "type" => "sql",
                "file" => basename($dataPath),
                "path" => $dataPath,
                "rows" => $count,
                "totalPages" => intval(ceil($count / self::DEFAULT_ROWS_PER_PAGE)),
            ];
        }

        #endregion

        //
        // NOTE: Add any additional log types, if desired!
        //

        // Finally, return all of the combined log metadata!
        return $logs;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     * @throws PluginNotInitializedException
     * @throws Exception
     */
    private static function getDataLog(Request $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();

        #region &channel=

        // Get the "channel", when applicable.
        $channel =
            ($params && array_key_exists("channel", $params))
                ? strtoupper($params["channel"])
                : "";

        // Build the "channel" query!
        $channelQuery =
            ($channel !== "")
                ? "WHERE channel = '$channel'"
                : "";

        #endregion

        #region &since= | &until=

        // Build the "since" date string, when applicable.
        $since =
            ($params && array_key_exists("since", $params) && strtotime($params["since"]) !== FALSE)
                ? (new DateTimeImmutable($params["since"]))->format(LogEntry::TIMESTAMP_FORMAT_DATETIME)
                : "";

        // Build the "until" date string, when applicable.
        $until =
            ($params && array_key_exists("until", $params) && strtotime($params["until"]) !== FALSE)
                ? (new DateTimeImmutable($params["until"]))->format(LogEntry::TIMESTAMP_FORMAT_DATETIME)
                : "";

        // Start to create the date range query string, based on the existence of a previous "channel" clause...
        $datesQuery =
            ($channel === "")
                ? (($since !== "" || $until !== "") ? "WHERE " : "")
                : (($since !== "" || $until !== "") ? "AND " : "");

        // IF both "since" and "until" have been provided, THEN append the correct query!
        if($since !== "" && $until !== "")
        {
            $datesQuery .= "timestamp BETWEEN '$since' AND '$until'";
        }

        // IF only "since" has been provided, THEN append the correct query!
        if($since !== "" && $until === "")
        {
            $datesQuery .= "timestamp >= '$since'";
        }

        // IF only "until" has been provided, THEN append the correct query!
        if($since === "" && $until !== "")
        {
            $datesQuery .= "timestamp <= '$until'";
        }

        #endregion

        #region &page=

        // Get a valid "page" parameter or set it to 0 when invalid or not provided!
        $page =
            ($params && array_key_exists("page", $params) && ctype_digit($params["page"]))
                ? intval($params["page"])
                : 0;

        // Get the total count of the possible query results.
        $results = Plugin::dbQuery(
            "
            SELECT COUNT(*) FROM logs
            $channelQuery
            $datesQuery
            ;
            "
        );

        // Calculate the total number of pages, based on a limit of DEFAULT_ROWS_PER_PAGE.
        $totalPages = ceil($results[0]["COUNT(*)"] / self::DEFAULT_ROWS_PER_PAGE);

        // Set the "page" to the last page, in cases where the page number is greater than the total pages.
        $page = ($page > $totalPages) ? $totalPages : $page;

        // Set the "page" to the first page, in cases where the page number is less than 0.
        $page = ($page < 0) ? 1 : $page;

        // NOTE: If the provided "page" was omitted or is 0, then all results will be returned without pagination!

        // Build the "page" query!
        $pagesQuery =
            ($page !== 0 ? "LIMIT ".self::DEFAULT_ROWS_PER_PAGE." " : "").
            ($page !== 0 ? "OFFSET ".intval(($page - 1) * self::DEFAULT_ROWS_PER_PAGE) : "");

        #endregion

        //
        // NOTE: Add additional parameter support as desired!
        //

        // Execute the combined query!
        $results = Plugin::dbQuery(
            "
            SELECT * FROM logs
            $channelQuery
            $datesQuery
            ORDER BY `id`
            $pagesQuery
            ;
            "
        );

        $name = strtolower($channel);
        $path = realpath(Plugin::getDataPath()."/plugin.db");
        $file = basename($path);

        // Create the starting data from the query results.
        $data[$name] = [
            "type" => "sql",
            "file" => $file,
            "path" => $path,
            "rows" => count($results),
            "totalPages" => $totalPages,

            "page" => $page,
        ];

        // Loop through each result, coercing the data as needed before appending it to the dataset...
        foreach($results as $result)
        {
            // Convert the data to valid LogEntry.
            $entry = LogEntry::fromRow($result);

            // NOTE: From the database, this should NEVER result in an invalid LogEntry!

            // Append this entry to the dataset.
            $data[strtolower($channel)]["entries"][] = $entry;
        }

        // Finally, return the results, even if there are none!
        return $response->withJson($data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     * @throws PluginNotInitializedException
     * @throws Exception
     */
    private static function getFileLog(Request $request, Response $response, array $args): Response
    {
        //$response->withHeader("Access-Control-Allow-Origin", "*");

        // IF no specific file was specified OR only a trailing slash was provided, THEN simply return "/logs"!
        if (!array_key_exists("file", $args) || !$args["file"])
            return $response->withJson(self::getLogsMetadata());

        // IF a file was specified, but does not exists, THEN return a 404!
        if (array_key_exists("file", $args) && $args["file"] &&
            !file_exists(Plugin::getLogsPath()."/{$args['file']}") &&
            !file_exists(Plugin::getLogsPath()."/../{$args['file']}"))
            return $response->withStatus(404, "Log file '{$args["file"]}' not found!");

        // IF a file was specified, but it is a special (or dot) file, THEN return a 404!
        if (array_key_exists("file", $args) && $args["file"] && (
                $args["file"] === "." || $args["file"] === ".." ||
                strpos($args["file"], ".") === 0))
            return $response->withStatus(404, "Log file '{$args["file"]}' is protected!");

        if (array_key_exists("file", $args) && $args["file"] &&
            strpos($args["file"], ".log") === false &&
            strpos($args["file"], ".txt") === false)
            return $response->withStatus(404, "Log file '{$args["file"]}' has an unsupported file extension!");

        // Make sure to fixup the "plugin.log" file, due to it being up one level in the folder structure.
        $file = $args["file"]; // === "plugin.log" ? "../plugin.log" : $args["file"];
        $path = realpath(Plugin::getLogsPath(). "/" . ($file === "plugin.log" ? "../plugin.log" : $file));

        $contents = file_get_contents($path);
        $lines = array_filter(explode("\n", $contents));

        $blocks = [];

        if(count($lines) % 3 === 0)
            for($i = 0; $i < count($lines); $i += 3)
                $blocks[] = implode("\n", array_slice($lines, $i, 3));

        $entries = LogEntry::fromText(implode("\n", $blocks));

        $standard = !($entries->count() === 0 && count($lines) !== 0);



        $params = $request->getQueryParams();

        #region &channel=

        // Get the "channel", when applicable.
        $channel =
            ($params && array_key_exists("channel", $params))
                ? strtoupper($params["channel"])
                : ($file === "plugin.log" ? "UCRM" : "");

        if($channel)
            $entries = $entries->where("channel", strtoupper($channel));

        #endregion

        #region &since= | &until=

        // Build the "since" date string, when applicable.
        $since =
            ($params && array_key_exists("since", $params) && strtotime($params["since"]) !== false)
                ? (new DateTimeImmutable($params["since"]))->format(LogEntry::TIMESTAMP_FORMAT_DATETIME)
                : "";

        // Build the "until" date string, when applicable.
        $until =
            ($params && array_key_exists("until", $params) && strtotime($params["until"]) !== false)
                ? (new DateTimeImmutable($params["until"]))->format(LogEntry::TIMESTAMP_FORMAT_DATETIME)
                : "";

        $entries = $entries->find(
            function(LogEntry $entry) use ($since, $until)
            {
                $timestampEntry = new DateTimeImmutable($entry->getTimestamp());
                $timestampSince = new DateTimeImmutable($since);
                $timestampUntil = new DateTimeImmutable($until);

                // IF both "since" and "until" have been provided, THEN append the correct query!
                if($since !== "" && $until !== "")
                    return ($timestampEntry >= $timestampSince && $timestampEntry <= $timestampUntil);

                // IF only "since" has been provided, THEN append the correct query!
                if($since !== "" && $until === "")
                    return ($timestampEntry >= $timestampSince);

                // IF only "until" has been provided, THEN append the correct query!
                if($since === "" && $until !== "")
                    return ($timestampEntry <= $timestampUntil);

                return true;
            }
        );

        #endregion

        #region &page=

        // Get a valid "page" parameter or set it to 0 when invalid or not provided!
        $page =
            ($params && array_key_exists("page", $params) && ctype_digit($params["page"]))
                ? intval($params["page"])
                : 0;

        $totalPages = ceil(($standard ? $entries->count() : count($lines)) / self::DEFAULT_ROWS_PER_PAGE);
        $page = ($page > $totalPages) ? $totalPages : $page;
        $page = ($page < 0) ? 1 : $page;

        if($page > 0)
            $entries = $entries->slice(
                intval(($page - 1) * self::DEFAULT_ROWS_PER_PAGE), self::DEFAULT_ROWS_PER_PAGE);

        #endregion

        // Initialize an empty array of logs.
        $logs = [];

        $data = [
            //"blocks" => $blocks,
            "type" => $standard ? "txt" : "raw",
            "file" => $file,
            "path" => $path,
            "rows" => $standard ? count($entries) : count($lines),
            "totalPages" => $totalPages,

            "page" => $page,
            "entries" => ($entries->count() === 0 && count($lines) > 0 ? $lines : $entries->elements()),
        ];

        $logs[basename($file, ".log")] = $data;

        return $response->withJson($logs);
    }

    /**
     * LogsController constructor.
     *
     * @param App $app The Slim Application for which to configure routing.
     */
    public function __construct(App $app)
    {
        // Get a local reference to the Slim Application's DI Container.
        $container = $app->getContainer();

        #region /logs/db

        // Handle GET queries to the database logs...
        $app->get("/logs/db", function (Request $request, Response $response, array $args) use ($container)
        {
            return self::getDataLog($request, $response, $args);
        });

        // Handle DELETE queries to the database logs...
        $app->delete("/logs/db", function (Request $request, Response $response, array $args) use ($app, $container)
        {
            $params = $request->getQueryParams();

            #region &channel=

            // Get the "channel", when applicable.
            $channel =
                ($params && array_key_exists("channel", $params))
                    ? strtoupper($params["channel"])
                    : "";

            // Build the "channel" query!
            $channelQuery =
                ( $channel !== "")
                    ? "WHERE channel = '$channel'"
                    : "";

            #endregion

            #region &since= | &until=

            // Build the "since" date string, when applicable.
            $since =
                ($params && array_key_exists("since", $params) && strtotime($params["since"]) !== false)
                    ? (new DateTimeImmutable($params["since"]))->format(LogEntry::TIMESTAMP_FORMAT_DATETIME)
                    : "";

            // Build the "until" date string, when applicable.
            $until =
                ($params && array_key_exists("until", $params) && strtotime($params["until"]) !== false)
                    ? (new DateTimeImmutable($params["until"]))->format(LogEntry::TIMESTAMP_FORMAT_DATETIME)
                    : "";

            // Start to create the date range query string, based on the existence of a previous "channel" clause...
            $datesQuery =
                ($channel === "")
                    ? (($since !== "" || $until !== "") ? "WHERE " : "")
                    : (($since !== "" || $until !== "") ? "AND " : "");

            // IF both "since" and "until" have been provided, THEN append the correct query!
            if($since !== "" && $until !== "")
                $datesQuery .= "timestamp BETWEEN '$since' AND '$until'";

            // IF only "since" has been provided, THEN append the correct query!
            if($since !== "" && $until === "")
                $datesQuery .= "timestamp >= '$since'";

            // IF only "until" has been provided, THEN append the correct query!
            if($since === "" && $until !== "")
                $datesQuery .= "timestamp <= '$until'";

            #endregion

            #region &page=

            // Get a valid "page" parameter or set it to 0 when invalid or not provided!
            $page =
                ($params && array_key_exists("page", $params) && ctype_digit($params["page"]))
                    ? intval($params["page"])
                    : 0;

            #endregion

            #region &delete=

            // Get a valid "delete" parameter, as we only delete the actual data when "true" or "yes" is provided!
            $delete =
                $params && array_key_exists("delete", $params) &&
                ($params["delete"] === "true" || $params["delete"] === "yes");

            if($delete)
            {
                /** @noinspection SqlWithoutWhere */
                // Execute the combined query!
                $results = Plugin::dbQuery(
                    "
                    DELETE FROM logs
                    $channelQuery
                    $datesQuery
                    ;
                    "
                );
            }

            #endregion

            //
            // NOTE: Add additional parameter support as desired!
            //

            Log::info("Log cleared!", strtoupper($channel));

            return self::getDataLog($request, $response, $args);
        });

        #endregion

        #region /logs/[{file}]

        // Handle GET queries to the file logs...
        $app->get("/logs/[{file}]", function (Request $request, Response $response, array $args) use ($container)
        {


            return self::getFileLog($request, $response, $args);
        });

        // Handle DELETE queries to the file logs...
        $app->delete("/logs/[{file}]", function (Request $request, Response $response, array $args) use ($container)
        {
            // IF no specific file was specified OR only a trailing slash was provided, THEN simply return "/logs"!
            if (!array_key_exists("file", $args) || !$args["file"])
                return $response->withJson(self::getLogsMetadata());

            // IF a file was specified, but does not exists, THEN return a 404!
            if (array_key_exists("file", $args) && $args["file"] &&
                !file_exists(Plugin::getLogsPath()."/{$args['file']}") &&
                !file_exists(Plugin::getLogsPath()."/../{$args['file']}"))
                return $response->withStatus(404, "Log file '{$args["file"]}' not found!");

            // IF a file was specified, but it is a special (or dot) file, THEN return a 404!
            if (array_key_exists("file", $args) && $args["file"] && (
                    $args["file"] === "." || $args["file"] === ".." ||
                    strpos($args["file"], ".") === 0))
                return $response->withStatus(404, "Log file '{$args["file"]}' is protected!");

            if (array_key_exists("file", $args) && $args["file"] &&
                strpos($args["file"], ".log") === false &&
                strpos($args["file"], ".txt") === false)
                return $response->withStatus(404, "Log file '{$args["file"]}' has an unsupported file extension!");

            // Make sure to fixup the "plugin.log" file, due to it being up one level in the folder structure.
            $file = $args["file"]; // === "plugin.log" ? "../plugin.log" : $args["file"];
            $path = realpath(Plugin::getLogsPath(). "/" . ($file === "plugin.log" ? "../plugin.log" : $file));

            $contents = file_get_contents($path);
            $lines = array_filter(explode("\n", $contents));

            $blocks = [];
            for($i = 0; $i < count($lines); $i += 3)
                $blocks[] = implode("\n", array_slice($lines, $i, 3));

            $entries = LogEntry::fromText(implode("\n", $blocks));

            $params = $request->getQueryParams();

            #region &channel=

            // Get the "channel", when applicable.
            $channel =
                ($params && array_key_exists("channel", $params))
                    ? strtoupper($params["channel"])
                    : ($file === "plugin.log" ? "UCRM" : "");

            if($channel)
                $entries = $entries->whereNot("channel", strtoupper($channel));

            #endregion

            #region &since= | &until=

            // Build the "since" date string, when applicable.
            $since =
                ($params && array_key_exists("since", $params) && strtotime($params["since"]) !== false)
                    ? (new DateTimeImmutable($params["since"]))->format(LogEntry::TIMESTAMP_FORMAT_DATETIME)
                    : "";

            // Build the "until" date string, when applicable.
            $until =
                ($params && array_key_exists("until", $params) && strtotime($params["until"]) !== false)
                    ? (new DateTimeImmutable($params["until"]))->format(LogEntry::TIMESTAMP_FORMAT_DATETIME)
                    : "";

            $entries = $entries->find(
                function(LogEntry $entry) use ($since, $until)
                {
                    $timestampEntry = new DateTimeImmutable($entry->getTimestamp());
                    $timestampSince = new DateTimeImmutable($since);
                    $timestampUntil = new DateTimeImmutable($until);

                    // IF both "since" and "until" have been provided, THEN append the correct query!
                    if($since !== "" && $until !== "")
                        return ($timestampEntry < $timestampSince && $timestampEntry > $timestampUntil);

                    // IF only "since" has been provided, THEN append the correct query!
                    if($since !== "" && $until === "")
                        return ($timestampEntry < $timestampSince);

                    // IF only "until" has been provided, THEN append the correct query!
                    if($since === "" && $until !== "")
                        return ($timestampEntry > $timestampUntil);

                    return true;
                }
            );

            #endregion

            #region &page=

            // Get a valid "page" parameter or set it to 0 when invalid or not provided!
            $page =
                ($params && array_key_exists("page", $params) && ctype_digit($params["page"]))
                    ? intval($params["page"])
                    : 0;

            #endregion

            #region &delete=

            // Get a valid "delete" parameter
            $delete =
                $params && array_key_exists("delete", $params) &&
                    ($params["delete"] === "true" || $params["delete"] === "yes");

            if($delete)
            {
                $contents = implode("\n", $entries->elements());
                file_put_contents($path, $contents, LOCK_EX);
            }

            #endregion

            //
            // NOTE: Add additional parameter support as desired!
            //

            Log::info("Log cleared!", strtoupper($channel));

            return self::getFileLog($request, $response, $args);
        });

        #endregion

        #region /logs

        // Handle GET queries to logs metadata...
        $app->get("/logs", function (Request $request, Response $response, array $args) use ($container)
        {
            //$response->withHeader("Access-Control-Allow-Origin", "*");
            return $response->withJson(self::getLogsMetadata());
        });

        #endregion
    }

}
