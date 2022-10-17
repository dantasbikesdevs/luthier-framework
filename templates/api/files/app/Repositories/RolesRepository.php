<?php

namespace App\Repositories;

use App\Repositories\AbstractRepository;
use App\Models\Entity\UserEntity;

class RolesRepository extends AbstractRepository
{
  /**
   * Nome da tabela de relação do repositório.
   */
  protected string $tableRelation;

  public function __construct()
  {
    $this->tableRelation = "USER_ROLES";
    parent::__construct("ROLES", "ID");
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
      ->where("ID_USER = :id")
      ->setParam("id", $id)
      ->run();
  }

  public function findByUser(UserEntity $user){
    $id = $user->getId();

    return $this->queryBuilder->select("r.*")
      ->from("$this->tableName r")
      ->innerJoinWith("$this->tableRelation ur", "ur.ID_ROLE = r.ID")
      ->where("ur.ID_USER = :id")
      ->setParam("id", $id)
      ->all();
  }

  public function findByNotInUser(UserEntity $user){
    $id = $user->getId();

    return $this->queryBuilder->customQuery(
      "SELECT * FROM ROLES r WHERE r.ID NOT IN (
      SELECT r.ID FROM ROLES r
      JOIN USER_ROLES    ur ON ur.ID_ROLE = r.ID
      JOIN USUARIOS_HOST uh ON uh.COD     = ur.ID_USER
      WHERE uh.COD = :id
      );"
    )
    ->setParam("id", $id)
    ->all();
  }
}