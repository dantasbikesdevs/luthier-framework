<?php

use Monolog\Level;

$channels = [
  "main" => [
    "file" => [
      "path"  => getenv("LOG_MAIN_FILE"),
      "level" => Level::Warning,
    ]
  ],
];
