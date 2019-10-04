<?php
declare(strict_types=1);

namespace App\Composer;

use Composer\Script\Event;

define("PROJECT_ROOT", dirname(__DIR__, 4));
define("PACKAGE_ROOT", dirname(__DIR__, 3));



final class Project
{


    /**
     * @param Event $event
     */
    public static function init(Event $event)
    {


    }

    private static function setEnvLine(string $contents, array $match, string $key, string $value): string
    {
        $contents = substr_replace($contents, $value, $match["value"]["start"], $match["value"]["length"]);
        $contents = substr_replace($contents, $key, $match["key"]["start"], $match["key"]["length"]);
        return $contents;
    }


    /**
     * @param string $contents
     * @param string $key
     * @param array $match
     * @return string|false
     */
    private static function getEnvLine(string $contents, string $key, array &$match = null)
    {
        // IF any RegEx match of KEY=[VALUE] exists in the string...
        if (preg_match_all("/^[ \t]*($key)[ \t]*=[ \t]*(.*)$/m", $contents, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE))
        {
            // NOTE: In the case of multiple duplicate entries, only get the value of the final entry...
            list($full, $key, $value) = array_pop($matches);

            // Set all the match meta-data.
            $match = [
                "key"   => [
                    "start"     => $key[1],
                    "length"    => strlen($key[0]),
                    "text"      => $key[0],
                ],
                "value" => [
                    "start"     => $value[1],
                    "length"    => strlen($value[0]),
                    "text"      => $value[0],
                ]
            ];

            // And return the assigned value!
            return $value[0];
        }

        // OTHERWISE, return false!
        return false;
    }



    /**
     * @param Event $event
     */
    public static function configureSFTP(Event $event)
    {
        // Initialize the configuration values.
        $host = "";
        $port = "";
        $user = "";
        $pass = "";

        // Initialize the existing content values.
        $contents = "";
        $matches = null;

        // IF an .env file exists...
        if($path = realpath(PACKAGE_ROOT . DIRECTORY_SEPARATOR . ".env"))
        {
            // ...THEN, get it's contents.
            $contents = file_get_contents($path);

            // NOTE: Currently, commented lines do NOT match and should likely be left this way!

            // Get match information for any existing line items.
            $host = self::getEnvLine($contents, "SFTP_HOST", $hostMatch) ?: "";
            $port = self::getEnvLine($contents, "SFTP_PORT", $portMatch) ?: "";
            $user = self::getEnvLine($contents, "SFTP_USER", $userMatch) ?: "";
            $pass = self::getEnvLine($contents, "SFTP_PASS", $passMatch) ?: "";

            // Construct some match meta-data, based on the results...
            $matches[$hostMatch["key"]["start"] ?? -4] = $hostMatch
                ?? [ "key" => [ "text" => "SFTP_HOST" ], "value" => null ];
            $matches[$portMatch["key"]["start"] ?? -3] = $portMatch
                ?? [ "key" => [ "text" => "SFTP_PORT" ], "value" => null ];
            $matches[$userMatch["key"]["start"] ?? -2] = $userMatch
                ?? [ "key" => [ "text" => "SFTP_USER" ], "value" => null ];
            $matches[$passMatch["key"]["start"] ?? -1] = $passMatch
                ?? [ "key" => [ "text" => "SFTP_PASS" ], "value" => null ];

            // NOTE: here we sort the matches in descending order, as we need to alter the content values in the reverse
            // order to not change the starting positions when new values are not the same lengths as the originals.
            krsort($matches, );
        }

        // Prompt for configuration values based on any existing values found.
        $host = $event->getIO()->ask("SFTP Host [$host]: ", $host);
        $port = $event->getIO()->ask("SFTP Port [$port]: ", $port);
        $user = $event->getIO()->ask("SFTP User [$user]: ", $user);
        $pass = $event->getIO()->ask("SFTP Pass [$pass]: ", $pass);

        // Do some quoting fix-ups to make sure there are no issues with the password.
        $pass = trim($pass, "\"");
        $pass = "\"".$pass."\"";

        // Initialize an array to store any values that need to be appended as opposed to altered.
        $append = [];

        // IF matches were previously found...
        if($matches)
        {
            // ...THEN loop through each match...
            foreach ($matches as $index => $match)
            {
                // ...AND determine the variable name based on the text value.
                $name = strtolower(str_replace("SFTP_", "", $match["key"]["text"]));

                // IF a match exists, THEN update it, OTHERWISE append it!
                if($match["value"])
                    $contents = self::setEnvLine($contents, $match, $match["key"]["text"], $$name);
                else
                    $append[$index] = $match["key"]["text"]."=".$$name."\n";
            }
        }
        else
        {
            // Append ALL of the missing values, as none of them currently exists.
            $append[-4] = "SFTP_HOST=".$host."\n";
            $append[-3] = "SFTP_PORT=".$port."\n";
            $append[-2] = "SFTP_USER=".$user."\n";
            $append[-1] = "SFTP_PASS=".$pass."\n";
        }

        // IF any items need to be appended...
        if(!empty($append))
        {
            // ...THEN comment the contents
            $contents .= "\n";
            $contents .= "# Generated by Project::configureSFTP()\n";

            // And append the configuration items.
            ksort($append);
            $contents .= implode("", $append);
        }

        // Finally, save the new contents to the original file or a new file as required.
        file_put_contents(PACKAGE_ROOT . DIRECTORY_SEPARATOR . ".env", $contents, LOCK_EX);
    }



}
