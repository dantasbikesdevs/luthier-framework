<?php

namespace App\Repositories;

use App\Interfaces\IRepository;
use Luthier\Database\Query;

abstract class AbstractRepository implements IRepository
{
  /**
   * Nome da tabela do repositório.
   */
  protected string $tableName;

  /**
   * Chave primária da tabela do repositório.
   */
  protected string $primaryKey;

  /**
   * Váriavel responsável por guardar a classe do model atual.
   */
  protected $model;

  /**
   * Váriavel responsável por guardar queryBuilder.
   */
  protected Query $queryBuilder;

  public function __construct(
    string $tableName,
    string $primaryKey,
    $model = null,
    ?Query $query = null,
  ) {
    $this->tableName    = $tableName;
    $this->primaryKey   = $primaryKey;
    $this->model        = $model;
    $this->queryBuilder = $query ?? new Query();
  }

  public function findOne(int $id)
  {
    return $this->queryBuilder->select()
      ->from($this->tableName)
      ->where("$this->primaryKey = :id")
      ->setParam("id", $id)
      ->asObject($this->model)
      ->first();
  }

  public function findAll(): array
  {
    return $this->queryBuilder->select()
      ->from($this->tableName)
      ->asObject($this->model)
      ->all();
  }

  public function create($model)
  {
    return $this->queryBuilder->insert($model, $this->tableName)
      ->returning([$this->primaryKey])
      ->run();
  }

  public function update($model, int $id)
  {
    return $this->queryBuilder->update($model, $this->tableName)
      ->where("$this->primaryKey = :id")
      ->setParam("id", $id)
      ->run();
  }

  public function destroy(int $id)
  {
    return $this->queryBuilder->delete($this->tableName)
      ->where("$this->primaryKey = :id")
      ->setParam("id", $id)
      ->run();
  }
}
