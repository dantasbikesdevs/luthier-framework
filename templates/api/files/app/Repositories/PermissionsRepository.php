<?php

namespace App\Repositories;

use App\Repositories\Repository;
use App\Models\Entity\UserEntity;
use Luthier\Database\Query;

class PermissionsRepository extends Repository
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

  public function __construct()
  {
    $this->tableName     = "PERMISSIONS";
    $this->tableRelation = "USER_PERMISSIONS";
    $this->primaryKey    = "ID";
    $this->model         = null;
    $this->queryBuilder  = new Query();
  }

  /**
   * Criando relação do usuário com a regra
   *
   * @param   UserEntity  $user
   * @param   $roleId
   */
  public function createRelation(UserEntity $user, $permissionId){
    $this->queryBuilder->insert([
      "ID_USER" => $user->getId(),
      "ID_PERMISSION" => $permissionId,
    ], $this->tableRelation);
  }

  public function removeRelations(UserEntity $user){
    $id = $user->getId();
    $this->queryBuilder
      ->delete()
      ->from($this->tableRelation)
      ->where("ID_USER = |$id|")
      ->run();
  }

  public function findOne($id){
    return $this->queryBuilder->select()
      ->from($this->tableName)
      ->where("ID = |$id|")
      ->first();
  }

  public function findByUser(UserEntity $user){
    $id = $user->getId();
    return $this->queryBuilder->select("p.*")
      ->from("$this->tableName p")
      ->innerJoinWith("$this->tableRelation up", "up.ID_PERMISSION = p.ID")
      ->where("up.ID_USER = |$id|")
      ->all();
  }

  public function findByNotInUser(UserEntity $user){
    $id = $user->getId();

    return $this->queryBuilder->customQuery(
      "SELECT * FROM PERMISSIONS p WHERE p.ID NOT IN (
      SELECT p.ID FROM PERMISSIONS p
      JOIN USER_PERMISSIONS up ON up.ID_PERMISSION = p.ID
      JOIN USUARIOS_HOST uh ON uh.COD =  up.ID_USER
      WHERE uh.COD = |$id|
      );"
    );
  }
}