<?php

namespace App\Model\Repository;

use App\Model\Entity\UserEntity;
use Luthier\Database\Database;
use Luthier\Security\Password;

class UserRepository
{
  private $databaseConnection;

  public function __construct()
  {
    $this->databaseConnection = new Database("test_users");
  }

  /**
   * Procura por um usuário pelo seu e-mail e retorna um array com o id do usuário, um objeto (UserEntity) e um a senha pós hash
   */
  public function findOneByEmail(string $email): array
  {
    $userArray = $this->databaseConnection->select(where: ["email = '$email'"])[0];

    if (!$userArray) return [];

    $userObject = new UserEntity($userArray["NAME"], $userArray["EMAIL"], $userArray["AGE"]);

    $result = [$userArray["ID"], $userObject, $userArray["PASSWORD"]];

    return $result;
  }

  /**
   * Cria um usuário a partir de um objeto (UserEntity) e uma senha pré hash e retorna um array com o id do usuário, um objeto (UserEntity)
   */
  public function create(UserEntity $user, string $password): array
  {
    $hashedPassword = Password::createHash($password);

    $userId = $this->databaseConnection->insert([
      "NAME" => $user->getName(),
      "EMAIL" => $user->getEmail(),
      "AGE" => $user->getAge(),
      "PASSWORD" => $hashedPassword
    ]);

    $userArray = $this->databaseConnection->select(where: ["id = $userId"]);

    if (!$userArray) return [];

    $userObject = new UserEntity($userArray["NAME"], $userArray["EMAIL"], $userArray["AGE"]);

    $result = [$userArray["ID"], $userObject];

    return $result;
  }
}
