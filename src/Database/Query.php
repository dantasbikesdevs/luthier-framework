<?php

namespace Luthier\Database;

use App\Database\ApplicationDatabase;
use Exception;
use Luthier\Utils\Transform;
use stdClass;

class Query
{
  private ?string $tableName;
  private mixed $model = null;
  private Database $database;
  private bool $forceOperation = false;
  private array $queryStore;

  public function __construct(Database $database = null)
  {
    $this->database = is_null($database) ? ApplicationDatabase::getInstance() : $database;
  }

  /* Obtém uma query string e seus valores*/
  public function __get($name)
  {
    if ($name == "query") {
      return $this->getSql();
    }
  }

  /**
   * Indica a tabela na qual as operações serão realizadas. Se omitido as operações serão executadas com a tabela
   * definida na criação do objeto Database. Adicione mais operações antes de executar a query com o método run().
   */
  public function from(string $tableName)
  {
    $this->tableName = $tableName;
    $from = " FROM " . $tableName;
    $this->addToQueryStore($from);
    return $this;
  }

  /**
   * Inicia query de select. Recebe os campos desejados (por padrão seleciona todos) e retorna um objeto Query.
   * Para executar adicione o método run() no final.
   */
  public function select(mixed $fields = "*", ?int $first = null, ?int $skip = null)
  {
    if (is_array($fields)) {
      $fields = implode(', ', $fields);
    }

    // Default
    $query = "SELECT $fields";

    // Ex: SELECT FIRST 10 * FROM products
    if ($first) {
      $query = "SELECT FIRST $first $fields";

      if ($skip) {
        // Ex: SELECT FIRST 10 SKIP 30 * FROM products
        $query = "SELECT FIRST $first SKIP $skip $fields";
      }
    }

    $this->addToQueryStore($query);

    return $this;
  }

  /**
   * Inicia query de insert. Recebe um array associativo ligando os campos a serem inseridos com
   * seus novos valores e retorna um objeto Query.
   * Para executar adicione o método run() no final.
   */
  public function insert(mixed $fieldsAndValues, ?string $tableName = null)
  {
    $table = $tableName ?? $this->tableName;

    if(is_object($fieldsAndValues)) {
      $fieldsAndValues = Transform::objectToArray($fieldsAndValues);
    }
    
    $queryFields = array_keys((array)$fieldsAndValues);
    $implodedFields = implode(',', $queryFields);

    /**
     * Transforma um array de valores como este: [1, "dev", 1.88]
     * Em um array de valores assim: ["|1|", "|dev|", "|1.88|"]
     * E depois em uma string assim: "|1|, |dev|, |1.88|"
     */
    $mappedValues = array_map(fn (mixed $value) => "|$value|", (array)$fieldsAndValues);
    $implodedValues = implode(',', $mappedValues);

    $query = "INSERT INTO $table ($implodedFields) VALUES ($implodedValues)";

    $this->addToQueryStore($query);

    return $this;
  }

  /**
   * Inicia query de update. Recebe um array associativo ligando os campos a serem atualizados com
   * seus novos valores e retorna um objeto Query.
   * Para executar adicione o método run() no final. Essa query não será executada sem where a menos que seja
   * removidas as guardas com forceDangerousCommand.
   */
  public function update(mixed $fieldsAndValues, ?string $tableName = null)
  {
    $table = $tableName ?? $this->tableName;

    if(is_object($fieldsAndValues)) {
      $fieldsAndValues = Transform::objectToArray($fieldsAndValues);
    }

    $queryFields = array_keys($fieldsAndValues);

    /**
     * Transforma um array de valores como este: ["age" => 18, "position" => "dev", "power" => 1.88]
     * Em um array de valores assim: ["age = |18|", "position = |dev|", "power = |1.88|"]
     */
    $mappedValues = array_map(fn (string $field, mixed $value) => " $field = |$value|", $queryFields, $fieldsAndValues);
    $implodedValues = implode(',', $mappedValues);

    $query = "UPDATE $table SET $implodedValues";

    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Inicia query de delete. Recebe um array associativo ligando os campos a serem atualizados com
   * seus novos valores e retorna um objeto Query.
   * Para executar adicione o método run() no final. Essa query não será executada sem where a menos que seja
   * removidas as guardas com forceDangerousCommand.
   */
  public function delete(?string $tableName = null)
  {
    $table = $tableName ?? $this->tableName;
    $query = "DELETE FROM |$table|";

    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Adiciona uma condição "where". Recebe uma condição no formato "campo = |valor|" e retorna um objeto Query.
   * Para executar adicione o método run() no final.
   */
  public function where(string $condition)
  {
    $query = "WHERE $condition ";

    $this->addToQueryStore($query);
    return $this;
  }


  /**
   * Adiciona uma condição "or" ao "where". Recebe uma condição no formato "campo = |valor|" e retorna um objeto Query.
   * Para executar adicione o método run() no final.
   */
  public function orWhere(string $condition)
  {
    $query = "OR $condition";

    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Adiciona uma condição "and" ao "where". Recebe uma condição no formato "campo = |valor|" e retorna um objeto Query.
   * Para executar adicione o método run() no final.
   */
  public function andWhere(string $condition)
  {
    $query = "AND $condition";

    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Adiciona uma ordenação aos resultados da query. Recebe um campo pelo qual ordenar e uma direção.
   * Para executar adicione o método run() no final.
   */
  public function orderBy(string $sort = "id", string $order = "asc")
  {
    $query = "ORDER BY |$sort| |$order|";

    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Une os resultados de duas tabelas em uma determinada condição. Para executar adicione o método run() no final.
   * Exemplo: (new Query("client"))->select()->joinWith(table: "receipt", on: "mainTable.id = thatTable.client_id")->run();
   *
   * Isso resulta em um query assim: "SELECT * FROM client JOIN receipt ON client.id = receipt.client_id"
   */
  public function joinWith(string $table, string $on, string $type = "")
  {
    $mainTable = $this->tableName;

    // Caso o usuário use esta notação para se relatar as tabelas principal e secundária
    // $on = preg_replace("mainTable", $mainTable, $on);
    // $on = preg_replace("thatTable", $table, $on);

    $query = "$type JOIN $table ON $on";

    // Ex: mainTable INNER JOIN thatTable ON mainTable.field = thatTable.field;
    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Une os resultados de duas tabelas em uma determinada condição. Para executar adicione o método run() no final.
   * Exemplo: (new Query("client"))->select()->innerJoinWith(table: "receipt", on: "mainTable.id = thatTable.client_id")->run();
   *
   * Isso resulta em um query assim: "SELECT * FROM client INNER JOIN receipt ON client.id = receipt.client_id"
   */
  public function innerJoinWith(string $table, string $on)
  {
    $this->joinWith($table, $on, "INNER");
    return $this;
  }

  /**
   * Une os resultados de duas tabelas em uma determinada condição. Para executar adicione o método run() no final.
   * Exemplo: (new Query("client"))->select()->leftJoinWith(table: "receipt", on: "mainTable.id = thatTable.client_id")->run();
   *
   * Isso resulta em um query assim: "SELECT * FROM client left JOIN receipt ON client.id = receipt.client_id"
   */
  public function leftJoinWith(string $table, string $on)
  {
    $this->joinWith($table, $on, "LEFT");
    return $this;
  }


  /**
   * Une os resultados de duas tabelas em uma determinada condição. Para executar adicione o método run() no final.
   * Exemplo: (new Query("client"))->select()->innerJoinWith(table: "receipt", on: "mainTable.id = thatTable.client_id")->run();
   *
   * Isso resulta em um query assim: "SELECT * FROM client INNER JOIN receipt ON client.id = receipt.client_id"
   */
  public function rightJoinWith(string $table, string $on)
  {
    $this->joinWith($table, $on, "INNER");
    return $this;
  }

  /**
   * Une os resultados de duas tabelas em uma determinada condição. Para executar adicione o método run() no final.
   * Exemplo: (new Query("client"))->select()->innerJoinWith(table: "receipt", on: "mainTable.id = thatTable.client_id")->run();
   *
   * Isso resulta em um query assim: "SELECT * FROM client INNER JOIN receipt ON client.id = receipt.client_id"
   */
  public function fullJoinWith(string $table, string $on)
  {
    $this->joinWith($table, $on, "INNER");
    return $this;
  }

  // TODO: LeftJoin
  // TODO: RightJoin

  /**
   * Cria uma query customizada. Recebe uma query com os valores não confiáveis entre pipes "|" e retorna um objeto Query.
   * Para executar adicione o método run() no final.
   */
  public function customQuery(string $queryString)
  {
    $this->addToQueryStore($queryString);
    return $this;
  }

  /**
   * Retorna a query SQL e os seus valores em ordem ["query" => "select ? from table", "values" => "*"]
   */
  public function getSql()
  {
    $queryTemplate =  implode(" ", $this->queryStore);
    return $this->extractQueryData($queryTemplate);
  }

  /**
   * Pula guarda de proteção contra operações arriscadas. Recebe uma justificativa que será armazenada no log, juntamente com a query executada.
   */
  public function forceDangerousCommand(string $justification)
  {
    $this->forceOperation = true;

    // TODO: Adicionar ao log
  }

  /**
   * Marca se o resultado da consulta será baseado em um objeto de determinada classe ou não.
   */
  public function asObject($model = stdClass::class)
  {
    $this->model = $model;
    return $this;
  }

  /**
   * Executa a query SQL retornando apenas o primeiro resultado.
   */
  public function first()
  {
    $queryData = $this->getSql();
    $this->queryStore = [];
    return $this->database->executeStatement($queryData["query"], $queryData["values"], false, $this->model);
  }

  /**
   * Executa a query SQL retornando todos os resultados.
   */
  public function all()
  {
    $queryData = $this->getSql();
    $this->queryStore = [];
    return $this->database->executeStatement($queryData["query"], $queryData["values"], true, $this->model);
  }

  /**
   * Executa a query SQL
   */
  public function run()
  {
    $queryData = $this->getSql();
    $this->queryStore = [];
    return $this->database->execute($queryData["query"], $queryData["values"]);
  }

   /**
   * Adiciona uma ordenação aos resultados da query. Recebe um campo pelo qual ordenar e uma direção.
   * Para executar adicione o método run() no final.
   */
  public function returning(array $fields = ["id"])
  {
    $implodedFields = implode(',', $fields);
    $query = "returning $implodedFields";

    $this->addToQueryStore($query);

    $queryData = $this->getSql();
    $this->queryStore = [];
    return $this->database->execute($queryData["query"], $queryData["values"])->fetchObject()->$implodedFields;
  }

  // ! PRIVATE METHODS

  /**
   * Transforma uma query string como esta: "select * from clients where id = |$id|" em "select * from clients where id = ?"
   * e separa os valores em um array. Retorna um array com "query" correspondendo a query limpa e "values" correspondendo aos
   * parâmetros na ordem correta.
   */
  private function extractQueryData(string $queryString)
  {
    $this->guards($queryString);

    $params = [];

    /**
     * Regex que identifica valores dentro de "|".
     */
    $patternVariable = '/\|(.*?)\|/';

    /**
     * Utiliza o regex anterior para separar os valores da query
     *
     * Entrada:
     * "select name, age from clients where id = |1| or user = '|lorem|'"
     *
     * Saída:
     * [
     *  "query" => "select name, age from clients where id = ? or user = '?'",
     *  "values" => [1, "lorem"]
     * ]
     */
    if (preg_match_all($patternVariable, $queryString, $matches)) {
      $cleanQueryString = preg_replace($patternVariable, '?', $queryString);
      $params = $matches[1];
    } else {
      $cleanQueryString = $queryString;
    }

    return [
      "query" => $cleanQueryString,
      "values" => $params
    ];
  }

  /**
   * Evita que operações perigosas sejam executadas. Pode ser pulado com forceDangerousCommand("justificativa")
   */
  private function guards(string $query)
  {
    if ($this->forceOperation) return;

    $query = strtolower($query);
    $dangerous = ["update", "delete"];

    foreach ($dangerous as $command) {
      $containsDangerousCommand = str_contains($query, $command);
      $doesNotContainWhere = !str_contains($query, "where");

      if ($containsDangerousCommand && $doesNotContainWhere) {
        throw new Exception("Erro ao tentar executar operação perigosa. '$query' sem where especificado.", 500);
      }
    }
  }

  /**
   * Adiciona uma query a lista de queries para serem executadas. Essa função altera diretamente uma propriedade
   * deste objeto.
   */
  private function addToQueryStore(string $query)
  {
    $this->queryStore[] = $query;
  }
}
