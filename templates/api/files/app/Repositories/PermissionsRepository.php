<?php

namespace App\Repositories;

use App\Repositories\AbstractRepository;
use App\Models\Entity\UserEntity;
use Luthier\Database\Query;

class PermissionsRepository extends AbstractRepository
{
  /**
   * Nome da tabela de relação do repositório.
   */
  protected string $tableRelation;

  public function __construct()
  {
    $this->tableRelation = "USER_PERMISSIONS";
    parent::__construct("PERMISSIONS", "ID");
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
      ->where("ID_USER = :id")
      ->setParam("id", $id)
      ->run();
  }

  public function findByUser(UserEntity $user){
    $id = $user->getId();
    return $this->queryBuilder->select("p.*")
      ->from("$this->tableName p")
      ->innerJoinWith("$this->tableRelation up", "up.ID_PERMISSION = p.ID")
      ->where("up.ID_USER = :id")
      ->setParam("id", $id)
      ->all();
  }

  public function findByNotInUser(UserEntity $user){
    $id = $user->getId();

    return $this->queryBuilder->customQuery(
      "SELECT * FROM PERMISSIONS p WHERE p.ID NOT IN (
      SELECT p.ID FROM PERMISSIONS p
      JOIN USER_PERMISSIONS up ON up.ID_PERMISSION = p.ID
      JOIN USUARIOS_HOST uh ON uh.COD =  up.ID_USER
      WHERE uh.COD = :id
      );"
    )
    ->setParam("id", $id)
    ->all();
  }
}