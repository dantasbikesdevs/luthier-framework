<?php

namespace App\Repositories;

use App\Database\ApplicationDatabase;
use App\Repositories\Repository;
use App\Models\Entity\UserEntity;
use Luthier\Database\Database;
use Luthier\Database\Query;
use Luthier\Database\Transaction;
use App\Repositories\PermissionsRepository;
use App\Repositories\RolesRepository;

class UserRepository extends Repository
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
    $transaction = new Transaction($this->database->getConnection());

    // Exemplo - Problema: passar argumento para função anônima...
    // Possível solução: Receber um array como segundo parâmetro com os argumentos da função anônima.
    $user = $transaction->panicRollback(function () use ($id) {
      return $this->queryBuilder->select()
        ->from($this->tableName)
        ->where("$this->primaryKey = |$id|")
        ->object($this->model)
        ->first();
    });

    return $user;
  }

  public function findAll()
  {
    return $this->queryBuilder->select()
      ->from($this->tableName)
      ->object($this->model)
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
      ->object($this->model)
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
    return $this->queryBuilder->update($user, $this->tableName)
      ->where("$this->primaryKey = |$id|")
      ->run();
  }

  public function getUserJWT($payload) {
    $user = $this->findOne($payload['id']);
    $user = $this->setUserPermissions($user);
    return $user;
  }

  public function setUserPermissions(UserEntity $user): UserEntity {
    $roles       = (new RolesRepository)->findByUser($user);
    $permissions = (new PermissionsRepository)->findByUser($user);

    $user->setRoles($roles);
    $user->setPermissions($permissions);

    return $user;
  }
}
