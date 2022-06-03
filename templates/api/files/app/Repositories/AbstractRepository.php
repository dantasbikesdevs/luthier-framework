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
  private Query $queryBuilder;

  public function findOne(int $id)
  {
    return $this->queryBuilder->select()
      ->from($this->tableName)
      ->where("$this->primaryKey = |$id|")
      ->asObject($this->model)
      ->first();
  }

  public function findAll()
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
      ->where("$this->primaryKey = |$id|")
      ->run();
  }

  public function destroy(int $id)
  {
    return $this->queryBuilder->delete($this->tableName)
      ->where("$this->primaryKey = |$id|")
      ->run();
  }
}
