<?php

namespace App\Repositories;

use App\Repositories\Repository;
use App\Models\Entity\UserEntity;
use Luthier\Database\Query;

class RolesRepository extends Repository
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
   * Váriavel responsável por guardar queryBuilder.
   */
  private Query $queryBuilder;

  public function __construct()
  {
    $this->tableName     = "ROLES";
    $this->tableRelation = "USER_ROLES";
    $this->primaryKey    = "ID";
    $this->queryBuilder  = new Query();
  }

  public function createRelation(UserEntity $user, $roleId){
    $this->queryBuilder->insert([
      "ID_USER" => $user->getId(),
      "ID_ROLE" => $roleId,
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

    return $this->queryBuilder->select("DISTINCT r.*")
      ->from("$this->tableName r")
      ->innerJoinWith("$this->tableRelation ur", "ur.ID_ROLE = r.ID")
      ->where("ur.ID_USER = |$id|")
      ->all();
  }

  public function findByNotInUser(UserEntity $user){
    $id = $user->getId();

    return $this->queryBuilder->customQuery(
      "SELECT * FROM ROLES r WHERE r.ID NOT IN (
      SELECT r.ID FROM ROLES r
      JOIN USER_ROLES    ur ON ur.ID_ROLE = r.ID
      JOIN USUARIOS_HOST uh ON uh.COD     = ur.ID_USER
      WHERE uh.COD = '${id}'
      );"
    );
  }
}