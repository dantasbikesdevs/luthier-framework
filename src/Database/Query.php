<?php

declare(strict_types=1);

namespace Luthier\Database;

use App\Database\ApplicationDatabase;
use Exception;
use Luthier\Exceptions\QueryException;
use Luthier\Reflection\Reflection;
use Luthier\Regex\Regex;
use PDOStatement;
use stdClass;

class Query
{
  /**
   * Tabela a ser consultada/executada.
   */
  private ?string $tableName;

  /**
   * Classe modelo da tabela, caso exista.
   */
  private mixed $model = null;

  /**
   * Instância de Database.
   */
  private Database $database;

  /**
   * Flag que força operação sem o uso de WHERE (Tome bastante cuidado).
   */
  private bool $forceOperation = false;

  /**
   * Atributo que armazena a string da query a ser executada.
   */
  private array $queryStore;

  /**
   * Atributo que armazena os parâmetros a serem inseridos na query.
   */
  private array $queryParams = [];

  public function __construct(Database $database = null)
  {
    $this->database = $database ?? ApplicationDatabase::getInstance();
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
  public function from(string $tableName): self
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
  public function select(mixed $fields = "*", ?int $first = null, ?int $skip = null): self
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
  public function insert(array|object $fieldsAndValues, ?string $tableName = null): self
  {
    if (empty($fieldsAndValues)) throw new QueryException("Nenhum campo foi informado para inserção.");

    $table = $tableName ?? $this->tableName;

    if (is_object($fieldsAndValues)) {
      $fieldsAndValues = Reflection::getValuesObjectToSQL($fieldsAndValues);
    }

    /**
     * Transforma um array de valores como este: ["age" => 18, "position" => "dev", "power" => 1.88]
     * Em um dois arrays de valores assim: ["age", "position", "power"] e [":age", ":position", ":power"]
     */
    foreach ($fieldsAndValues as $key => $value) {
      $queryFields[] = $key;
      $mappedValues[] = ":{$key}";

      $this->setParam($key, $value);
    }

    $implodedFields = implode(',', $queryFields);
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
  public function update(array|object $fieldsAndValues, ?string $tableName = null): self
  {
    if (empty($fieldsAndValues)) throw new QueryException("Nenhum campo foi informado para atualização.");

    $table = $tableName ?? $this->tableName;

    if (is_object($fieldsAndValues)) {
      $fieldsAndValues = Reflection::getValuesObjectToSQL($fieldsAndValues);
    }

    /**
     * Transforma um array de valores como este: ["age" => 18, "position" => "dev", "power" => 1.88]
     * Em um array de valores assim: ["age = :age", "position = :position", "power = :power"]
     */
    foreach ($fieldsAndValues as $key => $value) {
      $queryFields[] = "{$key} = :{$key}";

      $this->setParam($key, $value);
    }

    $implodedValues = implode(',', $queryFields);

    $query = "UPDATE $table SET $implodedValues";

    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Inicia query de delete. Recebe uma string com o nome da tabela de onde será deletado os registros.
   * Para executar adicione o método run() no final. Essa query não será executada sem where a menos que seja
   * removidas as guardas com forceDangerousCommand.
   */
  public function delete(?string $tableName = null): self
  {
    $table = $tableName ?? $this->tableName;
    $query = "DELETE FROM $table";

    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Adiciona uma condição "where". Recebe uma condição no formato "campo = [valor]" e retorna um objeto Query.
   * Para executar adicione o método run() no final.
   */
  public function where(string $condition): self
  {
    if (empty($condition)) throw new QueryException("A condição não pode estar vazia.");

    $query = "WHERE $condition";

    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Adiciona uma condição "or" ao "where". Recebe uma condição no formato "campo = [valor]" e retorna um objeto Query.
   * Para executar adicione o método run() no final.
   */
  public function orWhere(string $condition): self
  {
    if (empty($condition)) throw new QueryException("A condição não pode estar vazia.");

    $query = "OR $condition";

    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Adiciona uma condição "and" ao "where". Recebe uma condição no formato "campo = [valor]" e retorna um objeto Query.
   * Para executar adicione o método run() no final.
   */
  public function andWhere(string $condition): self
  {
    if (empty($condition)) throw new QueryException("A condição não pode estar vazia.");

    $query = "AND $condition";

    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Adiciona uma condição "where" com os filtros recebidos por array.
   *
   * Formas possíveis de passar os filtros:
   *
   * Exemplo 1: ["ID" => 1, "NAME" => "John"]
   * Resultado: WHERE (ID = ? AND NAME = ?)
   *
   * Exemplo 2: ["DATA_ENTREGA IS NOT NULL"]
   * Resultado: WHERE (DATA_ENTREGA IS NOT NULL)
   *
   * Exemplo 3: [["id > ?", [1]], ["name = ?", ["John"]]]
   * Resultado: WHERE (id > ? AND name = ?)
   *
   * Será retornado um registro caso corresponda a todos os filtros passados
   * Para executar adicione o método run() no final.
   *
   * Obs.: Caso algum parâmetro de um filtro passado seja vazio ou nulo, esse filtro será ignorado.
   * Recomendamos a não passar valores dinâmicos diretamente na string devido o risco de SQL injection.
   */
  public function filterWhere(array $filters): self
  {
    if (empty($filters)) return $this;

    $filterSQL = $this->transformFiltersInWhere($filters);
    if (empty($filterSQL)) return $this;

    $query = "WHERE ($filterSQL)";
    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Transforma array de filtros em uma cláusula de WHERE.
   */
  private function transformFiltersInWhere(array $filters): string
  {
    $filterSQL = "";
    foreach ($filters as $key => $filter) {
      if (is_string($key)) {
        $this->firstWayToAddFilters($key, $filter, $filterSQL); continue;
      }

      if (is_string($filter)) {
        $filterSQL .= "{$filter} AND "; continue;
      }

      if (is_array($filter[1])) {
        $this->thirdWayToAddFilters($key, $filter, $filters, $filterSQL); continue;
      }
    }

    return substr($filterSQL, 0, -5);
  }

  /**
   * Método responsável por transformar a primeira forma possível de filtro em uma cláusula de WHERE.
   *
   * Exemplo:
   * Entrada no método filterWhere: ["ID" => 1];
   * Saída: WHERE (ID = :ID)
   */
  private function firstWayToAddFilters(int|string $key, int|string|bool|float|null $value, string &$filterSQL): void
  {
    if ((empty($value) && $value != 0) || is_null($value)) return;

    $filterSQL .= "{$key} = :{$key} AND ";
    $this->setParam($key, $value);
  }

  /**
   * Método responsável por transformar a terceira forma possível de filtro em uma cláusula de WHERE
   *
   * Exemplo:
   * Entrada no método filterWhere: ["ID = :id", ["id" => 1]]; Saída: WHERE (ID = :id)
   */
  private function thirdWayToAddFilters(
    int|string $key,
    array $filter,
    array &$filters,
    string &$filterSQL
  ): void
  {
    $query = $filter[0] ?? "";
    $queryParams = $filter[1] ?? [];

    if (empty($query)) throw new QueryException("Filtro inválido. A query não pode estar vazia.");

    $queryParamsFiltered = array_filter($queryParams, fn ($value) => (!empty($value) || $value == 0) && !is_null($value));

    if (count($queryParamsFiltered) < count($queryParams)) {
      unset($filters[$key]); return;
    }

    $filterSQL .= "{$query} AND ";
    $this->setParams($queryParams);
  }

  /**
   * Método responsável por setar um parâmetro a query.
   * Ex.: $query->where("id = :id")->setParam("id", 1);
   * Ex.2: $query->where("id = ?")->setParam(1, 2);
   *
   * Obs.: Caso o nome do valor do parâmetro seja vazio, o parâmetro será ignorado.
   */
  public function setParam(int|string $param, int|string|bool|float|null $value): self
  {
    if (empty($param) && $param != 0) return $this;

    $this->queryParams[$param] = $value;
    return $this;
  }

  /**
   * Método responsável por setar um array com os parâmetros da query.
   * Ex.:  $query->where("id = ? and gender = ?")->setParams([1, "M"]);
   * Ex.2: $query->where("id = :id and gender = :gender")->setParams(["id" => 1, "gender" => "M"]);
   *
   * Obs.: Caso seja passado um array vazio, nada será alterado.
   */
  public function setParams(array $params): self
  {
    if (empty($params)) return $this;

    foreach ($params as $key => $value) {
      $this->setParam($key, $value);
    }

    return $this;
  }

  /**
   * Adiciona uma ordenação aos resultados da query. Recebe um campo pelo qual ordenar e uma direção.
   * Exemplo: "age DESC".
   * Para executar adicione o método run() no final.
   */
  public function orderBy(string $order): self
  {
    if (empty($order)) return $this;

    $query = "ORDER BY $order";

    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Une os resultados de duas tabelas em uma determinada condição. Para executar adicione o método run() no final.
   * Exemplo: (new Query("client c"))->select()->joinWith(table: "receipt r", on: "c.id = r.client_id")->run();
   *
   * Isso resulta em um query assim: "SELECT * FROM client JOIN receipt ON client.id = receipt.client_id"
   */
  public function joinWith(string $table, string $on, string $type = ""): self
  {
    // Caso o usuário use esta notação para se relatar as tabelas principal e secundária
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
  public function innerJoinWith(string $table, string $on): self
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
  public function leftJoinWith(string $table, string $on): self
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
  public function rightJoinWith(string $table, string $on): self
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
  public function fullJoinWith(string $table, string $on): self
  {
    $this->joinWith($table, $on, "INNER");
    return $this;
  }

  // TODO: LeftJoin
  // TODO: RightJoin

  /**
   * Agrupa os resultados da query. Recebe uma string com as tabelas referentes ao agrupamento.
   */
  public function groupBy(string $groupBy): self
  {
    if (empty($groupBy)) return $this;

    $query = "GROUP BY $groupBy";

    $this->addToQueryStore($query);
    return $this;
  }

  /**
   * Cria uma query customizada. Recebe uma query com os valores não confiáveis entre colchetes "[]" e retorna um objeto Query.
   * Para executar adicione o método run() no final.
   */
  public function customQuery(string $queryString): self
  {
    $this->addToQueryStore($queryString);
    return $this;
  }

  /**
   * Retorna a query SQL e os seus valores em ordem ["query" => "select * from table", "values" => []]
   */
  public function getSql(): array
  {
    $queryTemplate = implode(" ", $this->queryStore);
    $cleanQueryTemplate = preg_replace(Regex::$contiguousBlankSpaces, " ", $queryTemplate);
    $queryParams = $this->queryParams;

    $this->resetQuery();

    return [
      "query"  => $cleanQueryTemplate,
      "values" => $queryParams
    ];
  }

  /**
   * Pula guarda de proteção contra operações arriscadas. Recebe uma justificativa que será armazenada no log, juntamente com a query executada.
   */
  public function forceDangerousCommand(string $justification): self
  {
    $this->forceOperation = true;
    return $this;
  }

  /**
   * Marca se o resultado da consulta será baseado em um objeto de determinada classe ou não.
   */
  public function asObject($model = stdClass::class): self
  {
    $this->model = $model;
    return $this;
  }

  /**
   * Executa a query SQL retornando apenas o primeiro resultado.
   */
  public function first(): mixed
  {
    $queryData = $this->getSql();
    $model = $this->model;

    $this->guards($queryData["query"]);

    $this->resetQuery();
    return $this->database->executeStatement($queryData["query"], $queryData["values"], false, $model);
  }

  /**
   * Executa a query SQL retornando todos os resultados.
   */
  public function all(): mixed
  {
    $queryData = $this->getSql();
    $model = $this->model;

    $this->guards($queryData["query"]);

    $this->resetQuery();
    return $this->database->executeStatement($queryData["query"], $queryData["values"], true, $model);
  }

  /**
   * Executa a query SQL
   */
  public function run(): PDOStatement|bool
  {
    $queryData = $this->getSql();

    $this->guards($queryData["query"]);

    $this->resetQuery();
    return $this->database->execute($queryData["query"], $queryData["values"]);
  }

  /**
   * Adiciona uma ordenação aos resultados da query. Recebe um campo pelo qual ordenar e uma direção.
   * Para executar adicione o método run() no final.
   */
  public function returning(array $fields = ["id"]): mixed
  {
    $implodedFields = implode(',', $fields);
    $query = "returning $implodedFields";

    $this->addToQueryStore($query);
    return $this->run()->fetchObject()->$implodedFields;
  }

  // ! PRIVATE METHODS

  /**
   * Evita que operações perigosas sejam executadas. Pode ser pulado com forceDangerousCommand("justificativa")
   */
  private function guards(string $query): void
  {
    if ($this->forceOperation) return;

    $query = mb_strtolower($query);
    $dangerous = ["update", "delete"];

    foreach ($dangerous as $command) {
      $containsDangerousCommand = str_contains($query, $command);
      $doesNotContainWhere = !str_contains($query, "where");

      if ($containsDangerousCommand && $doesNotContainWhere) {
        throw new QueryException("Erro ao tentar executar operação perigosa. '$query' sem where especificado.");
      }
    }
  }

  /**
   * Adiciona uma query a lista de queries para serem executadas. Esse método altera diretamente uma propriedade
   * deste objeto.
   */
  private function addToQueryStore(string $query): self
  {
    $this->queryStore[] = $query;

    return $this;
  }

  /**
   * Método responsável por resetar os dados da query toda vez que a mesma for executada.
   */
  private function resetQuery(): void
  {
    $this->queryStore = [];
    $this->queryParams = [];
    $this->model = null;
  }
}
