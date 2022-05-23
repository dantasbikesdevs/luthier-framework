<?php

function databaseConfig(string $envType)
{
  if ($envType === "PRODUCTION") {
    return [
      "driver" => getenv("PRODUCTION_DRIVER"),
      "host" => getenv("PRODUCTION_DB_HOST"),
      "path" => getenv("PRODUCTION_DB_PATH"),
      "user" => getenv("PRODUCTION_DB_USER"),
      "password" => getenv("PRODUCTION_DB_PASSWORD"),
    ];
  }

  return [
    "driver" => getenv("DEV_DRIVER"),
    "host" => getenv("DEV_DB_HOST"),
    "path" => getenv("DEV_DB_PATH"),
    "user" => getenv("DEV_DB_USER"),
    "password" => getenv("DEV_DB_PASSWORD"),
  ];
}
