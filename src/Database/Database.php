<?php

namespace Luthier\Database;

use App\Database\ConfigDatabase;
use \PDOException;
use \PDO;
use PDOStatement;

class Database
{

  /**
   * Driver do banco de dados
   */
  private static string $driver;

  /**
   * Host de conexão com o banco de dados
   */
  private static string $host;

  /**
   * Caminho para o banco de dados
   */
  private static string $path;

  /**
   * Usuário do banco
   */
  private static string $user;

  /**
   * Senha de acesso ao banco de dados
   */
  private static string $pass;

  /**
   * Nome da tabela a ser manipulada
   */
  private string $table;

  /**
   * Instancia de conexão com o banco de dados
   */
  private PDO $connection;

  /**
   * Método responsável por configurar a classe
   */
  public static function config(string $driver, string $host, string $path, string $user, string $pass)
  {
    self::$driver = $driver;
    self::$path = $path;
    self::$host = $host;
    self::$user = $user;
    self::$pass = $pass;
  }

  /**
   * Define a tabela e instancia e conexão
   */
  public function __construct(?string $tableName = null, $application = false)
  {
    $this->table = $tableName;
    $this->setConnection();
  }

  // ! APIS PÚBLICAS

  /**
   * Retorna a conexão realizada com a tabela pelo PDO
   */
  public function getConnection(): PDO
  {
    return $this->connection;
  }

  /**
   * Inicia uma transação com o bando de dados (Precisa de um commit / rollback)
   */
  public function beginTransaction()
  {
    $this->connection->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
    return $this->connection->beginTransaction();
  }

  /**
   * Dá rollback em uma transação com o bando de dados (Precisa ser chamado depois de beginTransaction)
   */
  public function rollback()
  {
    $this->connection->rollBack();
  }

  /**
   * Dá commit em uma transação com o bando de dados (Precisa ser chamado depois de beginTransaction)
   */
  public function commit()
  {
    $this->connection->commit();
  }

  /**
   * Método responsável por executar queries dentro do banco de dados
   */
  public function execute(string $query, array $params = []): PDOStatement
  {
    try {
      $statement = $this->connection->prepare($query);
      $statement->execute($params);
      return $statement;
    } catch (PDOException $e) {
      switch ($e->getCode()) {
        case 23000:
          throw new \Exception('Dados já existentes!');
        default:
          throw new \Exception('DATABASE: ' . $e->getMessage() . "....QUERY:.. ${query}");
      }
    }
  }

  /**
   * Método responsável por executar queries personalizadas dentro do banco de dados
   */
  public function executeCustom(string $query, array $params = [])
  {
    try {
      $statement = $this->connection->prepare($query);
      $statement->execute($params);
      while ($finalResult = $statement->fetchAll(PDO::FETCH_ASSOC)) {
        return $finalResult;
      }
    } catch (PDOException $e) {
      throw new \Exception('Database ERROR: ' . $e->getMessage());
    }
  }

  /**
   * Método responsável por executar queries dentro do banco de dados
   */
  public function executeSelect(string $query, array $params = [])
  {
    $params = self::getValuesOfObjects($params);
    try {
      $statement = $this->connection->prepare($query);
      $statement->execute($params);
      $result = $statement->fetchAll(PDO::FETCH_ASSOC);
      return $result;
    } catch (PDOException $e) {
      throw new \Exception('Database ERROR: ' . $e->getMessage());
    }
  }

  /**
   * Método responsável por inserir dados no banco. Recebe valores no formato de um array associativo [ field => value ] e retorna o ID do item inserido
   */
  public function insert(mixed $values, $cod = "ID")
  {
    $values = self::getValuesOfObjects($values);
    // DADOS DA QUERY
    $fields = array_keys($values);
    $binds  = array_pad([], count($fields), '?');
    $return = $cod ? 'RETURNING ' . $cod : '';
    // MONTA A QUERY
    $query = 'INSERT INTO ' . $this->table . ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $binds) . ') ' . $return;
    // EXECUTA O INSERT
    return $this->execute($query, array_values($values))->fetchObject()->$cod;
  }

  /**
   * Select a partir de relações
   */
  public function findRelations(string $where = null, string $join = null, string $order = null,  ?Pagination $pagination = null, string $fields = '*')
  {
    // Query statements. Caso algum seja passado a string para rodar ele é gerada
    $joinStatement = $join ? "LEFT JOIN $join" : '';
    $whereStatement = $where ? "WHERE $where" : '';
    $orderStatement = $order ? "ORDER BY $order" : '';
    $limitStatement = $pagination ? $this->getLimit($pagination) : '';
    $statements = "$joinStatement $whereStatement $orderStatement $limitStatement";

    // MONTA A QUERY
    $query = "SELECT  $fields  FROM $this->table  $statements";

    // EXECUTA A QUERY
    $result = $this->execute($query);
    while ($finalResult = $result->fetchAll(PDO::FETCH_ASSOC)) {
      return $finalResult;
    }
  }

  /**
   * Método responsável por executar uma consulta no banco
   */
  public function select(string $fields = '*', array $where = [], array $inner = [], ?string $order = null, ?string $limit = null)
  {
    // DADOS DA QUERY
    if (count($inner)) {
      $inners = array_keys($inner);
      $ons = array_values($inner);
      unset($inner);
      for ($i = 0; $i < count($inners); $i++) {
        $inner .= count($inners) ? 'LEFT JOIN ' . $inners[$i] . ' ON ' . $ons[$i] . ' ' : '';
      }
    } else {
      $inner = '';
    }

    $whereStatement = !empty($where) ? 'WHERE ' : '';

    if (count($where) > 1) {
      foreach ($where as $key => $value) {
        $whereStatement .= $value . ' AND ';
      }
      $where = substr($whereStatement, 0, -4);
    } else {
      $whereStatement .= $where[0];
    }

    if (!strlen($whereStatement)) {
      $whereStatement = '';
    }

    $order = strlen($order) ? 'ORDER BY ' . $order : '';
    $limit = strlen($limit) ? 'LIMIT ' . $limit : '';

    // MONTA A QUERY
    $query = "SELECT $fields  FROM  $this->table " . $inner . "$whereStatement  $order  $limit";

    // EXECUTA A QUERY
    $result = $this->execute($query);

    while ($finalResult = $result->fetchAll(PDO::FETCH_ASSOC)) {
      return $finalResult;
    }
  }

  /**
   * Método responsável por executar uma consulta no banco
   */
  public function selectCustom(?string $where = null, ?string $order = null, ?Pagination $pagination = null, $fields = '*'): PDOStatement
  {
    // Query statements. Caso algum seja passado a string para rodar ele é gerada
    $whereStatement = $where ? "WHERE $where" : '';
    $orderStatement = $order ? "ORDER BY $order" : '';
    $limitStatement = $pagination ? $this->getLimit($pagination) : '';
    // Concatena os statements
    $statements = "$whereStatement $orderStatement $limitStatement";

    // Monta a query concatenando os parâmetros
    $query = "SELECT $fields FROM {$this->table} $statements";

    //EXECUTA A QUERY
    return $this->execute($query);
  }

  /**
   * Método responsável por executar atualizações no banco de dados
   */
  public function update(string $where, array $values)
  {
    $valuesArray = self::getValuesOfObjects($values);
    $fields = array_keys($valuesArray);

    // Monta a query
    $tableName = $this->table;

    // Esta interrogação será preenchida com valores pelo PDO
    $sqlTerm = '=?, ';

    // A operação abaixo resulta em um string assim: CAMPO=?, OUTRO_CAMPO=?
    // Quando processar a string da query veremos algo assim: CAMPO=VALOR, OUTRO_CAMPO=OUTRO_VALOR
    $termSeparatedFields = implode($sqlTerm, $fields);
    $query = "UPDATE $tableName SET $termSeparatedFields=? WHERE $where";

    // Executa a query
    $this->execute($query, array_values($valuesArray));
  }

  /**
   * Método responsável por excluir dados do banco
   */
  public function delete(string $where)
  {
    // MONTA A QUERY
    $query = "DELETE FROM  $this->table  WHERE $where";

    // EXECUTA A QUERY
    $this->execute($query);
  }

  // ! MÉTODOS INTERNOS

  /**
   * Método responsável por criar uma conexão com o banco de dados
   */
  private function setConnection()
  {
    try {
      $config = self::connectionAsString(
        self::$host,
        self::$path,
        self::$driver
      );

      $this->connection = new PDO($config, self::$user, self::$pass);

      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      throw new \Exception('Database ERROR: ' . $e->getMessage());
    }
  }

  private static function getValuesOfObjects($object)
  {
    if (!is_object($object)) return $object;
    foreach ($object as $key => $value) {
      $array[$key] = $value;
    }
    return $array;
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
