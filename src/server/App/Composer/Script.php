<?php
declare(strict_types=1);

namespace App\Composer;

use Composer\Script\Event;

class Script
{
    /**
     * @param Event $event
     */
    public static function example(Event $event)
    {
        $args = $event->getArguments();

        $event->getIO()->write("Do something useful!");

        foreach($args as $arg)
        {
            $event->getIO()->write($arg);
        }

    }

}
