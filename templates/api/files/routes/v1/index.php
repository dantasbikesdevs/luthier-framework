<?php

use \App\Controller\SecretsController;
use \Luthier\Http\Response;
use \Luthier\Http\Request;
use \Luthier\Http\Router;
use \App\Controller\UserController;

/**
 * Router existe aqui porque críamos este objeto em /public/index.php
 */

// Rota única
$router->get("/", [
  function (Request $request, Response $response) {
    return $response->send(["status" => "sucesso"]);
  }
]);

// Grupo
$router->group("/user", function (Router &$router) {
  $userController = new UserController();

  $router->post("/login", [
    fn (Request $request, Response $response) => $userController->signIn($request, $response)
  ]);

  $router->post("/register", [
    fn (Request $request, Response $response) => $userController->signUp($request, $response)
  ]);

  $router->get("/logout", [
    fn (Request $request, Response $response) => $userController->signOut($request, $response)
  ]);
});

// Middleware + Rota única
$router->middlewares(["jwt"])->get("/hi", [
  function (Request $request, Response $response) {
    return $response->ok()->send(["status" => "sucesso"]);
  }
]);

// Middlewares + Grupo
$router->middlewares(["jwt"])->group("/secrets", function (Router &$router) {
  $secretsController = new SecretsController();

  $router->get("/", [
    fn (Request $request, Response $response) => $secretsController->getMany($request, $response)
  ]);

  $router->get("/{id}", [
    fn (Request $request, Response $response, int $id) => $secretsController->getOne($request, $response, $id)
  ]);

  $router->put("/{id}", [
    fn (Request $request, Response $response, int $id) => $secretsController->update($request, $response, $id)
  ]);

  $router->post("/", [
    fn (Request $request, Response $response) => $secretsController->create($request, $response)
  ]);

  $router->delete("/{id}", [
    fn (Request $request, Response $response, int $id) => $secretsController->delete($request, $response, $id)
  ]);
});
