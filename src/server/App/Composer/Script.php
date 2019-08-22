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
        $event->getIO()->write("Do something useful!");
    }

}