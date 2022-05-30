<?php

namespace Luthier\Database;

use Exception;
use PDO;
use PDOException;
use stdClass;

/**
 * Necessário para se conectar com um banco real. Neste caso usamos um singleton para evitar sobrecargas de conexões com o banco.
 */
class Database implements IDatabase
{
  /**
   * Informações para se conectar ao SGBD
   */
  private DatabaseManager $databaseManager;

  /**
   * Instancia de conexão com o banco de dados
   */
  private PDO $connection;

  /**
   * Define a tabela e instancia e conexão
   */
  public function __construct(DatabaseManager $databaseManager)
  {
    $this->databaseManager = $databaseManager;
    $this->setConnection();
  }

  // ! APIS PÚBLICAS

  /**
   * Retorna a conexão realizada pelo PDO
   */
  public function getConnection(): PDO
  {
    return $this->connection;
  }

  // ! MÉTODOS INTERNOS

  /**
   * Método responsável por criar uma conexão com o banco de dados
   */
  private function setConnection()
  {
    try {
      // Obtém uma conexão com o banco em forma de uma string assim: firebird:dbname=trem/algo/banco.fdb;charset=utf8;dialect=3;
      $connectionPath = self::connectionAsString(
        $this->databaseManager->getHost(),
        $this->databaseManager->getPath(),
        $this->databaseManager->getDriver(),
      );

      // Conexão com o banco
      $this->connection = new PDO($connectionPath, $this->databaseManager->getUser(), $this->databaseManager->getPassword(), [
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      ]);
    } catch (PDOException $e) {
      throw new Exception('Erro ao tentar conexão com o banco de dados: ' . $e->getMessage());
    }
  }

  /**
   * Gera a string de conexão
   */
  private static function connectionAsString($host, $path, $driver = "firebird")
  {
    return "$driver:dbname=${host}:${path};charset=utf8;dialect=3;";
  }

  // * KERNEL

  /**
   * Método responsável por executar queries dentro do banco de dados que possuem retorno
   */
  public function executeStatement(string $query, array $params = [], $all = true, $model = stdClass::class)
  {
    try {
      // Essa etapa previne SQLi
      $statement = $this->connection->prepare($query);
      $statement->execute($params);

      $hasModel = !is_null($model);

      if ($all) {
        if ($hasModel) return $statement->fetchAll(PDO::FETCH_CLASS, $model);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
      }

      if ($hasModel) return $statement->fetchObject($model);
      return $statement->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      switch ($e->getCode()) {
        case 23000:
          throw new Exception('Dados já existentes!');
        default:
          throw new Exception('Erro em operação com o banco de dados: ' . $e->getMessage() . "\n ...QUERY: ${query}");
      }
    }
  }

  /**
   * Método responsável por executar queries dentro do banco de dados
   */
  public function execute(string $query, array $params = [])
  {
    try {
      // Essa etapa previne SQLi
      $statement = $this->connection->prepare($query);
      $statement->execute($params);
      return $statement;
    } catch (PDOException $e) {
      switch ($e->getCode()) {
        case 23000:
          throw new Exception('Dados já existentes!');
        default:
          throw new Exception('Erro em operação com o banco de dados: ' . $e->getMessage() . "\n ...QUERY: ${query}");
      }
    }
  }
}
