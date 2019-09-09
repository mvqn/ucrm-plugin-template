<?php /** @noinspection PhpUnused, PhpUndefinedClassInspection */
declare(strict_types=1);

use Robo\Tasks;

/**
 * Class RoboFile
 *
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 */
class RoboFile extends Tasks
{
    #region Hook Simulation

    /**
     * Simulates the Plugin being installed by executing the "hook_install.php" script.
     */
    public function hookInstall()
    {
        $this->say("Executing the Plugin's install hook...");
        $this->_exec("php ./src/hook_install.php");
    }

    /**
     * Simulates the Plugin being removed by executing the "hook_remove.php" script.
     */
    public function hookRemove()
    {
        $this->say("Executing the Plugin's remove hook...");
        $this->_exec("php ./src/hook_remove.php");
    }

    /**
     * Simulates the Plugin being updated by executing the "hook_update.php" script.
     */
    public function hookUpdate()
    {
        $this->say("Executing the Plugin's update hook...");
        $this->_exec("php ./src/hook_update.php");
    }

    /**
     * Simulates the Plugin being enabled by executing the "hook_enable.php" script.
     */
    public function hookEnable()
    {
        $this->say("Executing the Plugin's enable hook...");
        $this->_exec("php ./src/hook_enable.php");
    }

    /**
     * Simulates the Plugin being disabled by executing the "hook_disable.php" script.
     */
    public function hookDisable()
    {
        $this->say("Executing the Plugin's disable hook...");
        $this->_exec("php ./src/hook_disable.php");
    }

    /**
     * Simulates the Plugin being configured by executing the "hook_configure.php" script.
     */
    public function hookConfigure()
    {
        $this->say("Executing the Plugin's configure hook...");
        $this->_exec("php ./src/hook_configure.php");
    }

    #endregion

    #region Environment

    /**
     * @param string $key
     * @param string $label
     * @param string $file
     * @param bool $quotes
     */
    private function envEntry(string $key, string $label, string &$file, bool $quotes = false)
    {
        $found = preg_match('/^('.$key.')[ \t]*=[ \t]*(.*)$/m', $file, $matches);
        $match = $found ? $matches[2] : "";
        $plain = $found ? str_replace("\"", "", $match) : "";

        $value = str_replace("\"", "", $this->askDefault($label, $plain ?? ""));

        if($found && $plain !== $value)
        {
            $file = str_replace(
                $matches[0],
                str_replace(
                    $matches[2] === "\"\"" ? "\"\"" : str_replace("\"", "", $matches[2]),
                    $matches[2] === "\"\"" ? "\"$value\"" : $value,
                    $matches[0]
                ),
                $file
            );
        }

        if(!$found)
            $file .= "\n$key=".($quotes ? "\"" : "")."$value".($quotes ? "\"" : "")."\n";
    }

    #endregion

    #region SFTP

    /**
     * Prompts the developer for SFTP Configuration and saves or updates the results in the "src/.env" file.
     */
    public function sftpConfigure()
    {
        $envPath = __DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR.".env.local";
        $envFile = file_exists($envPath) ? file_get_contents($envPath) : "";

        $this->envEntry("SFTP_HOST", "SFTP Host", $envFile);
        $this->envEntry("SFTP_PORT", "SFTP Port", $envFile);
        $this->envEntry("SFTP_USER", "SFTP User", $envFile);
        $this->envEntry("SFTP_PASS", "SFTP Pass", $envFile, true);

        file_put_contents($envPath, $envFile, LOCK_EX);
    }



    #endregion


}
