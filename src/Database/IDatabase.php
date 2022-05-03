<?php

namespace Luthier\Database;

use PDO;

interface IDatabase
{
  /**
   * Método responsável por configurar a classe
   */
  public static function init(DatabaseManager $databaseManager, DatabaseUser $user): DatabaseSingleton;

  /**
   * Retorna a conexão realizada com a tabela pelo PDO
   */
  public static function getInstance(): PDO;
}
