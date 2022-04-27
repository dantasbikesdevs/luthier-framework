<?php

namespace Luthier\Database;

use Exception;
use Luthier\Database\Database;
use Luthier\Utils\Transform;

class Query
{
  private string $tableName;
  private $database;
  private $forceOperation;
  private array $queryStore;

  public function __construct(Database $database)
  {
    $this->database = $database;
    $this->tableName = $table ?? $database->getTableName();
  }


  /**
   * Indica a tabela na qual as operações serão realizadas. Se omitido as operações serão executadas com a tabela
   * definida na criação do objeto Database. Adicione mais operações antes de executar a query com o método run().
   */
  public function withTable(string $tableName)
  {
    $this->tableName = $tableName;
    return $this;
  }

  /**
   * Inicia query de select. Recebe os campos desejados (por padrão seleciona todos) e retorna um objeto Query.
   * Para executar adicione o método run() no final.
   */
  public function select(mixed $fields = "*")
  {
    if (is_array($fields)) {
      $fields = implode(', ', $fields);
    }

    $table = $this->tableName;

    $query = "SELECT $fields from |$table|";

    $this->addToQueryStore($query);

    return $this;
  }

  /**
   * Inicia query de insert. Recebe um array associativo ligando os campos a serem inseridos com
   * seus novos valores e retorna um objeto Query.
   * Para executar adicione o método run() no final.
   */
  public function insert(array $fieldsAndValues, ?string $tableName = null)
  {
    $table = $tableName ?? $this->tableName;

    $queryFields = array_keys($fieldsAndValues);
    $implodedFields = implode(',', $queryFields);

    /**
     * Transforma um array de valores como este: [1, "dev", 1.88]
     * Em um array de valores assim: ["|1|", "|dev|", "|1.88|"]
     * E depois em uma string assim: "|1|, |dev|, |1.88|"
     */
    $mappedValues = array_map(fn (mixed $value) => "|$value|", $fieldsAndValues);
    $implodedValues = implode(',', $mappedValues);

    $query = "INSERT INTO |$table| ($implodedFields) VALUES ($implodedValues)";

    $this->addToQueryStore($query);

    return $this;
  }

  /**
   * Inicia query de update. Recebe um array associativo ligando os campos a serem atualizados com
   * seus novos valores e retorna um objeto Query.
   * Para executar adicione o método run() no final. Essa query não será executada sem where a menos que seja
   * removidas as guardas com forceDangerousCommand.
   */
  public function update(array $fieldsAndValues, ?string $tableName = null)
  {
    $table = $tableName ?? $this->tableName;

    $queryFields = array_keys($fieldsAndValues);

    /**
     * Transforma um array de valores como este: ["age" => 18, "position" => "dev", "power" => 1.88]
     * Em um array de valores assim: ["age = |18|", "position = |dev|", "power = |1.88|"]
     */
    $mappedValues = array_map(fn (string $field, mixed $value) => "|$field| = |$value|", $queryFields, $fieldsAndValues);
    $implodedValues = implode(',', $mappedValues);

    $query = "UPDATE |$table| SET $implodedValues";

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
   * Adiciona uma ordenação aos resultados da query. Recebe um campo pelo qual ordenar e uma direção.
   * Para executar adicione o método run() no final.
   */
  public function returning(array $fields = ["id"])
  {
    $implodedFields = implode(',', $fields);

    $this->query .= "returning $implodedFields";
    return $this;
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
   * Executa a query SQL
   */
  public function run()
  {
    $queryData = $this->extractQueryData($this->query);
    echo "\n\n\n";
    print_r($queryData);
    echo "\n\n\n";
    exit;
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
