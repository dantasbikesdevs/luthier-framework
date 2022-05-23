<?php

namespace App\Controller;

use App\Model\Entity\UserEntity;
use App\Model\Repository\UserRepository;
use App\Utils\Validate;
use Luthier\Http\Cookie;
use Luthier\Http\Request;
use Luthier\Http\Response;
use Luthier\Security\Jwt;
use Luthier\Security\Password;

class UserController
{
  private $repository;

  public function __construct()
  {
    $this->repository = new UserRepository();
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

    [$userId, $user, $hashedPassword] = $this->repository->findOneByEmail($email);

    $unauthorizedMessage = "Login não permitido. Credenciais incorretas.";

    if (!$user) {
      return $response->unauthorized()->send(["error" => $unauthorizedMessage]);
    }

    $isValid = Password::matches($password, $hashedPassword);

    if (!$isValid) return $response->unauthorized()->send(["error" => $unauthorizedMessage]);

    $payload = [
      "id" => $userId,
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

    $unauthorizedMessage = "Usuário já existe. Faça login.";

    if ($user) {
      return $response->unauthorized()->send(["error" => $unauthorizedMessage]);
    }

    $newUser = new UserEntity($username, $email, $age);

    [$createdUser, $userId]  = $this->repository->create($newUser, $password);

    $payload = [
      "id" => $userId,
      "username" => $createdUser->getName(),
    ];

    $body = [
      "is_logged" => true,
      "name" => $createdUser->getName(),
    ];

    $jwt = Jwt::encode($payload);

    Cookie::send(["jwt" => $jwt]);

    return $response->ok()->send($body);
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
