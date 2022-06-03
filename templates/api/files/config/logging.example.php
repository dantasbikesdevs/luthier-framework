<?php

use Monolog\Level;

$channels = [
  "main" => [
    "file" => [
      "path"  => getenv("LOG_MAIN_FILE"),
      "level" => Level::Warning,
    ],
    "email" => [
      "host"     => getenv("LOG_MAIN_EMAIL_HOST"),
      "port"     => getenv("LOG_MAIN_EMAIL_PORT"),
      "username" => getenv("LOG_MAIN_EMAIL_USERNAME"),
      "password" => getenv("LOG_MAIN_EMAIL_PASSWORD"),
      "from"     => getenv("LOG_MAIN_EMAIL_FROM"),
      "to"       => getenv("LOG_MAIN_EMAIL_TO"),
      "subject"  => getenv("LOG_MAIN_EMAIL_SUBJECT"),
      "level"    => Level::Critical,
    ],
    "telegram" => [
      ""  => getenv("LOG_MAIN_TELEGRAM_TOKEN"),
      "channel" => getenv("LOG_MAIN_TELEGRAM_CHANNEL"),
      "level"   => Level::Emergency,
    ],
  ],
];
