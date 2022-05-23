<?php

namespace App\Model\Repository;

use App\Model\Entity\SecretEntity;
use Exception;
use Luthier\Database\Database;

class SecretRepository
{
  private $databaseConnection;

  public function __construct()
  {
    $this->databaseConnection = new Database("test_secrets");
  }

  /**
   * Procura por uma mensagem secreta pelo seu ID e retorna um array com um objeto (SecretEntity) e seu id
   */
  public function findAll()
  {
    $secretArray = $this->databaseConnection->select();

    if (!$secretArray) return [];

    $results = [];
    foreach ($secretArray as $index => $secret) {
      $results[] = [
        "id" => $secret["ID"],
        "title" => $secret["TITLE"],
        "message" => $secret["MESSAGE"],
      ];
    }

    return ["secrets" => $results];
  }

  /**
   * Procura por uma mensagem secreta pelo seu ID e retorna um array com um objeto (SecretEntity) e seu id
   */
  public function findById(int $id)
  {
    $secret = $this->databaseConnection->select(where: ["id = $id"])[0];

    if (!$secret) return [];

    $result = [
      "id" => $secret["ID"],
      "title" => $secret["TITLE"],
      "message" => $secret["MESSAGE"],
    ];

    return ["secret" => $result];
  }

  /**
   * Cria um usuário a partir de um objeto (UserEntity) e uma senha pré hash e retorna um array com o id do usuário, um objeto (UserEntity)
   */
  public function create(SecretEntity $secret): array
  {
    $secretId = $this->databaseConnection->insert([
      "TITLE" => $secret->getTitle(),
      "MESSAGE" => $secret->getMessage(),
    ]);

    $result = [
      "id" => $secretId,
      "title" => $secret->getTitle(),
      "message" => $secret->getMessage(),
    ];

    return ["secret" => $result];
  }

  /**
   * Procura por uma mensagem secreta pelo seu ID, a deleta e então retorna true para bem sucedido ou lança uma exceção caso mal sucedido
   */
  public function deleteById(int $id): array
  {
    try {
      $this->databaseConnection->delete("id = $id");

      $result = [
        "deleted" => true,
        "id" => $id,
      ];
    } catch (\Throwable $error) {
      $message = $error->getMessage();
      throw new Exception("Não foi possível deletar o elemento com id $id. Error: $message", 500);
    }

    return ["secret" => $result];
  }

  /**
   * Atualiza uma mensagem secreta pelo seu ID com os dados passados em um objeto (SecretEntity) e então retorna true para bem sucedido
   * ou lança uma exceção caso mal sucedido
   */
  public function updateById(int $id, SecretEntity $secret): array
  {
    $update = [
      "TITLE" => $secret->getTitle(),
      "MESSAGE" => $secret->getMessage()
    ];

    try {
      $this->databaseConnection->update("id = $id", $update);

      $result = [
        "id" => $id,
        "title" => $secret->getTitle(),
        "message" => $secret->getMessage(),
      ];

      return ["secret" => $result];
    } catch (\Throwable $error) {
      $message = $error->getMessage();
      throw new Exception("Não foi possível deletar o elemento com id $id. Error: $message", 500);
    }
  }
}
