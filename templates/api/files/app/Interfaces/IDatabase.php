<?php

namespace App\Interfaces;

use Luthier\Database\Database;
use Luthier\Database\DatabaseManager;
use PDO;

interface IDatabase {
  public static function init(DatabaseManager $databaseManager);
  public static function getInstance(): Database;
  public static function getConnection(): PDO;
}