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
    #region Hooks

    function hookInstall()
    {
        $this->say("Executing the Plugin's install hook...");
        $this->_exec("php ./src/hook_install.php");
    }

    function hookRemove()
    {
        $this->say("Executing the Plugin's remove hook...");
        $this->_exec("php ./src/hook_remove.php");
    }

    function hookUpdate()
    {
        $this->say("Executing the Plugin's update hook...");
        $this->_exec("php ./src/hook_update.php");
    }

    function hookEnable()
    {
        $this->say("Executing the Plugin's enable hook...");
        $this->_exec("php ./src/hook_enable.php");
    }

    function hookDisable()
    {
        $this->say("Executing the Plugin's disable hook...");
        $this->_exec("php ./src/hook_disable.php");
    }

    function hookConfigure()
    {
        $this->say("Executing the Plugin's configure hook...");
        $this->_exec("php ./src/hook_configure.php");
    }

    #endregion





    function sftpConfigure()
    {
        $host = $this->ask("SFTP Host");
        $port = $this->askDefault("SFTP Port", "22");
        $user = $this->ask("SFTP User");
        $pass = $this->ask("SFTP Pass", true);

        $envPath = __DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR.".env.local";
        $envFile = file_exists($envPath) ? file_get_contents($envPath) : "";

        if(preg_match('/^(SFTP_HOST)[ \t]*=[ \t]*(.*)$/m', $envFile, $matches))
            $envFile = str_replace($matches[0], str_replace($matches[2], $host, $matches[0]), $envFile);
        else
            $envFile .= "\nSFTP_HOST=$host\n";

        if(preg_match('/^(SFTP_PORT)[ \t]*=[ \t]*(.*)$/m', $envFile, $matches))
            $envFile = str_replace($matches[0], str_replace($matches[2], $host, $matches[0]), $envFile);
        else
            $envFile .= "SFTP_PORT   = $port\n";

        if(preg_match('/^(SFTP_USER)[ \t]*=[ \t]*(.*)$/m', $envFile, $matches))
            $envFile = str_replace($matches[0], str_replace($matches[2], $host, $matches[0]), $envFile);
        else
            $envFile .= "SFTP_USER   = $user\n";

        if(preg_match('/^(SFTP_PASS)[ \t]*=[ \t]*(.*)$/m', $envFile, $matches))
            $envFile = str_replace($matches[0], str_replace($matches[2], $host, $matches[0]), $envFile);
        else
            $envFile .= "SFTP_PASS   = $pass\n";

        file_put_contents($envPath, $envFile, LOCK_EX);
    }



}
