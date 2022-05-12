<?php

namespace Luthier\Database;

use Exception;
use Luthier\Database\DatabaseUser;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Necessário para se conectar com um banco real. Neste caso usamos um singleton para evitar sobrecargas de conexões com o banco.
 */
class DatabaseSingleton implements IDatabase
{
  /**
   * Única instância verdadeira do objeto
   */
  private static DatabaseSingleton $instance;

  /**
   * Informações para se conectar ao SGBD
   */
  private static DatabaseManager $databaseManager;

  /**
   * Informações de permissão de acesso ao banco
   */
  private static DatabaseUser $databaseUser;

  /**
   * Instancia de conexão com o banco de dados
   */
  private PDO $connection;

  /**
   * Método responsável por configurar a classe
   */
  public static function init(DatabaseManager $databaseManager, DatabaseUser $user): DatabaseSingleton
  {
    self::$databaseManager = $databaseManager;
    self::$databaseUser = $user;

    return new DatabaseSingleton();
  }

  /**
   * Define a tabela e instancia e conexão
   */
  private function __construct()
  {
    if (!$this->databaseManager) {
      throw new Exception("Erro ao usar módulo Database sem configurar informações do gerenciador de banco de dados.", 500);
    }

    if (!$this->databaseUser) {
      throw new Exception("Erro ao usar módulo Database sem configurar informações do usuário do banco de dados.", 500);
    }

    if (!isset(self::$instance)) {
      self::$instance = $this->setConnection(self::$databaseManager,  self::$databaseUser);
    }

    return self::$instance;
  }

  private function __clone()
  {
    return $this->__construct();
  }

  // ! APIS PÚBLICAS

  /**
   * Retorna a conexão realizada com a tabela pelo PDO
   */
  public static function getInstance(): PDO
  {
    return self::$connection;
  }

  // * KERNEL

  /**
   * Método responsável por executar queries dentro do banco de dados
   */
  public function executeStatement(string $query, array $params = [])
  {
    try {
      // Essa etapa previne SQLi
      $statement = $this->connection->prepare($query);
      $statement->execute($params);

      // Retorna os resultados aos poucos
      while ($finalResult = $statement->fetchAll(PDO::FETCH_ASSOC)) {
        yield $finalResult;
      }
    } catch (PDOException $e) {
      switch ($e->getCode()) {
        case 23000:
          throw new Exception('Dados já existentes!');
        default:
          throw new Exception('Erro em operação com o banco de dados: ' . $e->getMessage() . "\n ...QUERY: ${query}");
      }
    }
  }

  // ! MÉTODOS INTERNOS

  /**
   * Método responsável por criar uma conexão com o banco de dados
   */
  private function setConnection(DatabaseManager $sgbd, DatabaseUser $user)
  {
    try {
      // Obtém uma conexão com o banco em forma de uma string assim: firebird:dbname=trem/algo/banco.fdb;charset=utf8;dialect=3;
      $config = self::connectionAsString(
        $sgbd->getHost(),
        $sgbd->getPath(),
        $sgbd->getDriver(),
      );

      // Conexão com o banco
      return new PDO($config, $user->getUser(), $user->getPassword(), [
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      ]);
    } catch (PDOException $e) {
      throw new Exception('Erro ao tentar conexão com o banco de dados: ' . $e->getMessage());
    }
  }

  private function getLimit(Pagination $pagination)
  {
    return $pagination->getLimit();
  }

  /**
   * Gera a string de conexão
   */
  private static function connectionAsString($host, $path, $driver = "firebird")
  {
    return "$driver:dbname=${host}:${path};charset=utf8;dialect=3;";
  }
}
