<?php
declare(strict_types=1);

// Configure any defaults to be populated in data/config.json after Plugin installation.

$defaults = [
    "development" => true,
    // ...
];

// Save the default settings.
//file_put_contents(__DIR__ . "/data/config.json", json_encode($defaults));



$dbPath = __DIR__."/data/plugin.db";

$pluginDatabase = new PDO("sqlite:".$dbPath);

$pluginDatabase->exec(
    "
    CREATE TABLE IF NOT EXISTS logs
    (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        timestamp   TEXT,
        channel     TEXT    NOT NULL,
        level       INTEGER NOT NULL,
        level_name  TEXT    NOT NULL,
        message     TEXT,
        context     TEXT,
        extra       TEXT                
    );
    "
);




