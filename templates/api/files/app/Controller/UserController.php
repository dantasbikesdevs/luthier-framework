<?php

namespace App\Controller;

use App\Models\Entity\UserEntity;
use App\Repositories\UserRepository;
use App\Utils\Validate;
use Luthier\Http\Cookie;
use Luthier\Http\Request;
use Luthier\Http\Response;
use Luthier\Security\Jwt;
use Luthier\Security\Password;

class UserController
{
  /**
   * Repositório de usuários.
   */
  private UserRepository $repository;

  public function __construct()
  {
    $this->repository = new UserRepository();
  }

  public function findOne(Request $request, Response $response, int $id)
  {
    Validate::notEmpty($id);

    $user = $this->repository->findOne($id);

    if (!$user) {
      return $response->notFound()->send(["error" => "Usuário não encontrado."]);
    }

    return $response->ok()->send($user);
  }

  public function findAll(Request $request, Response $response)
  {
    $users = $this->repository->findAll();

    if (!$users) {
      return $response->notFound()->send(["error" => "Nenhum usuário foi encontrado."]);
    }

    return $response->ok()->send($users);
  }

  /**
   * Exemplo de um signIn de usuário usando JWT
   */
  public function signIn(Request $request, Response $response)
  {
    $data = $request->getPostVars();
    $email = $data["email"];
    $password = $data["password"];

    Validate::email($email);
    Validate::password($password);

    $user = $this->repository->findOneByEmail($email);

    $unauthorizedMessage = "Login não permitido. Credenciais incorretas.";

    if (!$user) {
      return $response->unauthorized()->send(["error" => $unauthorizedMessage]);
    }

    $isValid = Password::matches($password, $user->getPassword());

    if (!$isValid) return $response->unauthorized()->send(["error" => $unauthorizedMessage]);

    $payload = [
      "id" => $user->getId(),
      "username" => $user->getName(),
    ];

    $body = [
      "is_logged" => true,
      "name" => $user->getName(),
    ];

    $jwt = Jwt::encode($payload);

    Cookie::send(["jwt" => $jwt]);

    return $response->send($body)->ok();
  }

  /**
   * Exemplo de um signUp de usuário usando JWT
   */
  public function signUp(Request $request, Response $response)
  {
    $data = $request->getPostVars();
    $username = $data["name"];
    $age = $data["age"];
    $email = $data["email"];
    $password = $data["password"];

    Validate::notEmpty($username);
    Validate::notEmpty($age);
    Validate::email($email);
    Validate::password($password);

    $user = $this->repository->findOneByEmail($email);

    $unauthorizedMessage = "Este e-mail já está em uso. Tente outro.";

    if ($user) {
      return $response->unauthorized()->send(["error" => $unauthorizedMessage]);
    }

    $dataUser = [
      "NAME" => $username,
      "AGE" => $age,
      "EMAIL" => $email,
      "PASSWORD" => Password::createHash($password),
    ];

    $newUser = new UserEntity($dataUser);

    $user = $this->repository->create($newUser);

    $payload = [
      "id" => $user->getId(),
      "username" => $user->getName(),
    ];

    $body = [
      "is_logged" => true,
      "name" => $user->getName(),
    ];

    $jwt = Jwt::encode($payload);

    Cookie::send(["jwt" => $jwt]);

    return $response->ok()->send($body);
  }

  public function update(Request $request, Response $response) {
    $data = $request->getPostVars();
    $username = $data["name"];
    $age      = $data["age"];
    $email    = $data["email"];
    $password = $data["password"];

    Validate::notEmpty($username);
    Validate::notEmpty($age);
    Validate::email($email);
    Validate::password($password);

    $user = $this->repository->findOne($email);

    $unauthorizedMessage = "Este e-mail já está em uso. Tente outro.";

    if ($user) {
      return $response->unauthorized()->send(["error" => $unauthorizedMessage]);
    }

    $dataUser = [
      "NAME"     => $username,
      "AGE"      => $age,
      "EMAIL"    => $email,
      "PASSWORD" => Password::createHash($password),
    ];

    $newUser = new UserEntity($dataUser);

    $user = $this->repository->create($newUser);

    $payload = [
      "id"       => $user->getId(),
      "username" => $user->getName(),
    ];

    $body = [
      "is_logged" => true,
      "name" => $user->getName(),
    ];

    $jwt = Jwt::encode($payload);

    Cookie::send(["jwt" => $jwt]);

    return $response->ok()->send("Usuário atualizado com sucesso.");
  }

  /**
   * Exemplo de um signOut de usuário usando JWT
   */
  public function signOut(Request $request, Response $response)
  {
    $request->setPayload([]);

    Cookie::send(["jwt" => ""], "1.s");

    $body = [
      "is_logged" => false,
    ];

    return $response->ok()->send($body);
  }
}
