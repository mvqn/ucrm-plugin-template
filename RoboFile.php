<?php /** @noinspection PhpUnused, PhpUndefinedClassInspection */
declare(strict_types=1);
require_once __DIR__."/src/server/vendor/autoload.php";

use MVQN\Robo\Task\Sftp\Exceptions\ConfigurationMissingException;
use MVQN\Robo\Task\Sftp\Exceptions\ConfigurationParsingException;
use MVQN\Robo\Task\Sftp\Exceptions\OptionMissingException;
use MVQN\SFTP\Exceptions\AuthenticationException;
use MVQN\SFTP\Exceptions\InitializationException;
use MVQN\SFTP\Exceptions\LocalStreamException;
use MVQN\SFTP\Exceptions\MissingExtensionException;
use MVQN\SFTP\Exceptions\RemoteConnectionException;
use MVQN\SFTP\Exceptions\RemoteStreamException;
use Robo\Tasks;

/**
 * Class RoboFile
 *
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 */
class RoboFile extends Tasks
{
    private const REMOTE_PLUGIN_PATH = "/home/unms/data/ucrm/ucrm/data/plugins";


    use MVQN\Robo\Task\Sftp\Tasks;
    use MVQN\Robo\Task\Packer\Tasks;

    #region Plugin

    private function getPluginName(): string
    {
        $manifest = json_decode(file_get_contents(__DIR__."/src/manifest.json"), true);
        return $manifest["information"]["name"];
    }

    private function getPluginVersion(): string
    {
        $manifest = json_decode(file_get_contents(__DIR__."/src/manifest.json"), true);
        return $manifest["information"]["version"];
    }

    private function isValid(): bool
    {
        // TODO: Validation!
        return true;
    }


    /**
     * @throws AuthenticationException
     * @throws ConfigurationMissingException
     * @throws ConfigurationParsingException
     * @throws InitializationException
     * @throws LocalStreamException
     * @throws MissingExtensionException
     * @throws OptionMissingException
     * @throws RemoteConnectionException
     * @throws RemoteStreamException
     */
    public function initSync()
    {
        $plugin = $this->getPluginName();

        $remoteBase = self::REMOTE_PLUGIN_PATH."/$plugin";
        $localBase  = __DIR__."\\src";

        $this->taskSftpGet()

            ->loadConfiguration()

            ->funcConfiguration(
                function(array $current)
                {
                    if ($current["host"] === "" || $current["port"] === "" ||
                        $current["user"] === "" || $current["pass"] === "")
                        $current = $this->askSftpConfiguration(__DIR__, "sftp.config.json");

                    return $current;
                }
            )

            //->setRemoteBase()
            //->setLocalBase()

            ->maps([
                "$remoteBase/ucrm.json"                 => "$localBase\\ucrm.json",
                "$remoteBase/data/config.json"          => "$localBase\\data\\config.json",
                "$remoteBase/data/plugin.log"           => "$localBase\\data\\plugin.log",
                "$remoteBase/data/plugin.db"            => "$localBase\\data\\plugin.db",
                "$remoteBase/server/App/Settings.php"   => "$localBase\\server\\App\\Settings.php",


            ])

            ->run();

        /*
        POSTGRES_PASSWORD=PHwUv2P1vldQwUn68Vwv9FT4s2SUQAqlmvK36gA6GONQZSUU
        POSTGRES_USER=ucrm
        POSTGRES_DB=ucrm
        POSTGRES_HOST=ucrm.dev.mvqn.net
        POSTGRES_PORT=5432
        */

    }

    #region CLIENT

    /**
     * @return bool
     */
    public function clientRun(): bool
    {
        return $this->taskExec("cd src/client/ && yarn serve")->run()->wasSuccessful();
    }

    /**
     * @return bool
     */
    public function clientUpdate(): bool
    {
        return $this->taskExec("cd src/client/ && yarn upgrade")->run()->wasSuccessful();
    }

    /**
     * @return bool
     */
    public function clientBuild(): bool
    {
        return $this->taskExec("cd src/client/ && yarn build")->run()->wasSuccessful();
    }

    /**
     * @return bool
     */
    public function clientDeploy(): bool
    {
        return $this->clientUpdate() && $this->clientBuild();
    }

    #endregion







    public function bundle()
    {
        $options = [
            "folder" => __DIR__."/src/",
            "ignore" => __DIR__."/src/.zipignore",
            "output" => [
                "name" => $this->getPluginName(), //."_".$this->getPluginVersion(),
                "path" => __DIR__,
            ],
        ];

        $this->taskPackerBundle($options)
            ->run();
    }

    #endregion

    #region Hook Simulation

    /**
     * Simulates the Plugin being installed by executing the "hook_install.php" script.
     */
    public function hookInstall(): void
    {
        $this->say("Executing the Plugin's install hook...");
        $this->_exec("php ./src/hook_install.php");
    }

    /**
     * Simulates the Plugin being removed by executing the "hook_remove.php" script.
     */
    public function hookRemove(): void
    {
        $this->say("Executing the Plugin's remove hook...");
        $this->_exec("php ./src/hook_remove.php");
    }

    /**
     * Simulates the Plugin being updated by executing the "hook_update.php" script.
     */
    public function hookUpdate(): void
    {
        $this->say("Executing the Plugin's update hook...");
        $this->_exec("php ./src/hook_update.php");
    }

    /**
     * Simulates the Plugin being enabled by executing the "hook_enable.php" script.
     */
    public function hookEnable(): void
    {
        $this->say("Executing the Plugin's enable hook...");
        $this->_exec("php ./src/hook_enable.php");
    }

    /**
     * Simulates the Plugin being disabled by executing the "hook_disable.php" script.
     */
    public function hookDisable(): void
    {
        $this->say("Executing the Plugin's disable hook...");
        $this->_exec("php ./src/hook_disable.php");
    }

    /**
     * Simulates the Plugin being configured by executing the "hook_configure.php" script.
     */
    public function hookConfigure(): void
    {
        $this->say("Executing the Plugin's configure hook...");
        $this->_exec("php ./src/hook_configure.php");
    }

    #endregion

    #region Environment

    /**
     * Checks for existing entries in the "src/.env.local" file and then either updates or creates the them as needed.
     *
     * @param string $key       The ENV key.
     * @param string $label     The label to use in the Robo Task Prompt when asking for the value.
     * @param string $contents  The contents of the ENV file on which to interact.
     * @param bool $quotes      An optional flag denoting forceful use of quotes around a value during creation.
     */
    private function setEnv(string $key, string $label, string &$contents, bool $quotes = false): void
    {
        $found = preg_match('/^('.$key.')[ \t]*=[ \t]*(.*)$/m', $contents, $matches);
        $match = $found ? $matches[2] : "";
        $plain = $found ? str_replace("\"", "", $match) : "";

        $value = str_replace("\"", "", $this->askDefault($label, $plain ?? ""));

        if($found && $plain !== $value)
        {
            $contents = str_replace(
                $matches[0],
                str_replace(
                    $matches[2] === "\"\"" ? "\"\"" : str_replace("\"", "", $matches[2]),
                    $matches[2] === "\"\"" ? "\"$value\"" : $value,
                    $matches[0]
                ),
                $contents
            );
        }

        if(!$found)
            $contents .= "\n$key=".($quotes ? "\"" : "")."$value".($quotes ? "\"" : "")."\n";
    }

    /**
     * @param string $key
     * @return string|null
     */
    private function getEnv(string $key): ?string
    {
        $envPath = __DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR.".env.local";
        $envFile = file_exists($envPath) ? file_get_contents($envPath) : "";

        if(preg_match('/^('.$key.')[ \t]*=[ \t]*(.*)$/m', $envFile, $matches))
            return str_replace("\"", "", $matches[2]);

        return null;
    }

    #endregion

    #region SFTP



    /**
     * Prompts the developer for SFTP Configuration, saves or updates the results in a "sftp.config.json" file and then
     * adds the relative path to the ".gitignore" file.
     */
    public function sftpConfigure(): void
    {
        $this->askSftpConfiguration(__DIR__, "sftp.config.json");
    }

    /**
     * @param string $remote
     * @param string $local
     * @throws AuthenticationException
     * @throws InitializationException
     * @throws LocalStreamException
     * @throws MissingExtensionException
     * @throws RemoteConnectionException
     * @throws RemoteStreamException
     * @throws ConfigurationMissingException
     * @throws ConfigurationParsingException
     * @throws OptionMissingException
     */
    public function sftpGet(string $remote, string $local)
    {
        $plugin = $this->getPluginName();

        $remote = strpos($remote, "/") === 0 ? $remote : self::REMOTE_PLUGIN_PATH."/$plugin/$remote";
        $local = strpos($remote, ":\\") !== false ? $local : __DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."$local";

        $this->taskSftpGet()

            ->loadConfiguration()

            ->funcConfiguration(
                function(array $current) use ($remote, $local)
                {
                    if ($current["host"] === "" || $current["port"] === "" ||
                        $current["user"] === "" || $current["pass"] === "")
                        $current = $this->askSftpConfiguration(__DIR__, "sftp.config.json");

                    return $current;
                }
            )

            ->map($remote, $local)

            ->run();
    }

    /**
     * @param string $local
     * @param string $remote
     *
     * @throws AuthenticationException
     * @throws ConfigurationMissingException
     * @throws ConfigurationParsingException
     * @throws InitializationException
     * @throws LocalStreamException
     * @throws MissingExtensionException
     * @throws OptionMissingException
     * @throws RemoteConnectionException
     * @throws RemoteStreamException
     */
    public function sftpPut(string $local, string $remote)
    {
        $plugin = $this->getPluginName();

        $remote = strpos($remote, "/") === 0 ? $remote : self::REMOTE_PLUGIN_PATH."/$plugin/$remote";
        $local = strpos($remote, ":\\") !== false ? $local : __DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."$local";

        $this->taskSftpPut()

            ->loadConfiguration()

            ->funcConfiguration(
                function(array $current) use ($remote, $local)
                {
                    if ($current["host"] === "" || $current["port"] === "" ||
                        $current["user"] === "" || $current["pass"] === "")
                        $current = $this->askSftpConfiguration(__DIR__, "sftp.config.json");

                    return $current;
                }
            )

            ->map($local, $remote)

            // TODO: Change file permissions???

            ->run();
    }

    #endregion


}



