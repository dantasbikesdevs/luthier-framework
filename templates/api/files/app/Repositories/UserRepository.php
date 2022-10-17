<?php

namespace App\Repositories;

use App\Repositories\AbstractRepository;
use App\Models\Entity\UserEntity;
use App\Repositories\PermissionsRepository;
use App\Repositories\RolesRepository;

class UserRepository extends AbstractRepository
{
  public function __construct()
  {
    parent::__construct("USERS", "ID", UserEntity::class);
  }

  /**
   * Procura por um usuário pelo seu e-mail e retorna um array com o id do usuário, um objeto (UserEntity) e um a senha pós hash
   */
  public function findOneByEmail(string $email): ?UserEntity
  {
    $user = $this->queryBuilder->select()
      ->from($this->tableName)
      ->where("EMAIL = :email")
      ->setParam("email", $email)
      ->asObject($this->model)
      ->first();

    if (!$user) return null;

    return $user;
  }

  /**
   * Cria um usuário a partir de um objeto (UserEntity) e uma senha pré hash e retorna um array com o id do usuário, um objeto (UserEntity)
   */
  public function create($user)
  {
    $id = $this->queryBuilder->insert($user, $this->tableName)
      ->returning([$this->primaryKey]);

    $user = $this->findOne($id);

    return $user;
  }

  public function update($user, int $id)
  {
    $checkEmail = $this->findOneByEmail($user->getEmail());

    if($checkEmail && $checkEmail->getId() != $id) {
      throw new \InvalidArgumentException("Este e-mail já está em uso. Tente outro.", 409);
    }

    return $this->queryBuilder->update($user, $this->tableName)
      ->where("$this->primaryKey = :id")
      ->setParam("id", $id)
      ->run();

  }

  public function getUserJWT($payload) {
    $user = $this->findOne($payload['id']);

    if(!$user) return [];

    $userWithPermissions = $this->setUserPermissions($user);
    return $userWithPermissions;
  }

  public function setUserPermissions(UserEntity $user): UserEntity {
    $roles       = (new RolesRepository)->findByUser($user);
    $permissions = (new PermissionsRepository)->findByUser($user);

    $user->setRoles($roles);
    $user->setPermissions($permissions);

    return $user;
  }
}
