<?php

namespace App\Repositories;

use App\Database\ApplicationDatabase;
use App\Repositories\AbstractRepository;
use App\Models\Entity\UserEntity;
use Luthier\Database\Database;
use Luthier\Database\Query;
use App\Repositories\PermissionsRepository;
use App\Repositories\RolesRepository;

class UserRepository extends AbstractRepository
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

  /**
   * Váriavel responsável por guardar a instância do banco de dados.
   */
  private Database $database;

  public function __construct()
  {
    $this->tableName    = "USERS";
    $this->primaryKey   = "ID";
    $this->model        = UserEntity::class;
    $this->database     = ApplicationDatabase::getInstance();
    $this->queryBuilder = new Query();
  }

  public function findOne(int $id)
  {
    $user = $this->queryBuilder->select()
      ->from($this->tableName)
      ->where("$this->primaryKey = |$id|")
      ->asObject($this->model)
      ->first();

    return $user;
  }

  public function findAll()
  {
    return $this->queryBuilder->select()
      ->from($this->tableName)
      ->asObject($this->model)
      ->all();
  }

  /**
   * Procura por um usuário pelo seu e-mail e retorna um array com o id do usuário, um objeto (UserEntity) e um a senha pós hash
   */
  public function findOneByEmail(string $email): UserEntity | array
  {
    $user = $this->queryBuilder->select()
      ->from($this->tableName)
      ->where("EMAIL = |$email|")
      ->asObject($this->model)
      ->first();

    if (!$user) return [];

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
      ->where("$this->primaryKey = |$id|")
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
