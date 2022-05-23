<?php

namespace App\Controller;

use App\Model\Entity\SecretEntity;
use App\Model\Repository\SecretRepository;
use Exception;
use \Luthier\Http\Request;
use \Luthier\Http\Response;

class SecretsController
{
  private SecretRepository $repository;

  public function __construct()
  {
    $this->repository = new SecretRepository();
  }

  /**
   * Obtém um segredo
   */
  public function getOne(Request $request, Response $response, int $id): Response
  {
    $secret = $this->repository->findById($id);
    return $response->ok()->send($secret);
  }

  /**
   * Obter múltiplos segredos em uma lista
   */
  public function getMany(Request $request, Response $response): Response
  {
    $secrets = $this->repository->findAll();
    return $response->ok()->send($secrets);
  }

  /**
   * Cria um segredo a partir de um título e uma mensagem
   */
  public function create(Request $request, Response $response): Response
  {
    $data = $request->getPostVars();

    $secret = new SecretEntity($data["title"], $data["message"]);

    try {
      $recentSecret = $this->repository->create($secret);
    } catch (\Throwable $error) {
      $errorMessage = $error->getMessage();
      return $response->internalServerError("Erro ao tentar criar segredo. $errorMessage");
    }

    return $response->created("Segredo criado com sucesso.")->send($recentSecret);
  }

  /**
   * Atualiza um segredo com um título e uma mensagem nova a partir do id
   */
  public function update(Request $request, Response $response, int $id): Response
  {
    $data = $request->getPostVars();

    $secret = new SecretEntity($data["title"], $data["message"]);


    try {
      $newSecret = $this->repository->updateById($id, $secret);
    } catch (\Throwable $error) {
      $errorMessage = $error->getMessage();
      return $response->internalServerError("Erro ao tentar criar segredo. $errorMessage");
    }

    return $response->created("Segredo criado com sucesso.")->send($newSecret);
  }

  /**
   * Apaga um segredo a partir do seu id
   */
  public function delete(Request $request, Response $response, int $id): Response
  {
    $secret = $this->repository->findById($id);

    if (!$secret) return $response->notFound()->send(["error" => "Segredo com id = $id não encontrado."]);

    $secret = $this->repository->deleteById($id);

    return $response->ok()->send($secret);
  }
}
