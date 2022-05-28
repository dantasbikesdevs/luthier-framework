<?php

namespace App\Database;

use App\Interfaces\IDatabase;
use Luthier\Database\Database;
use Luthier\Database\DatabaseManager;
use PDO;

/**
 * Necessário para se conectar com um banco real. Neste caso usamos um singleton para evitar sobrecargas de conexões com o banco.
 */
class ApplicationDatabase implements IDatabase
{
  /**
   * Instancia da classe Database
   */
  private static Database $instance;

  /**
   * Método responsável por configurar a classe
   */
  public static function init(DatabaseManager $databaseManager)
  {
    self::$instance = new Database($databaseManager);
  }

  /**
   * Retorna a instancia da classe Database
   */
  public static function getInstance(): Database {
    return self::$instance;
  }

  /**
   * Retorna a conexão com o banco de dados da instancia Database
   */
  public static function getConnection(): PDO
  {
    return self::$instance->getConnection();
  }
}