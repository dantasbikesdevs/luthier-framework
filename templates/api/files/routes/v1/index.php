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
$router->group("/users", function (Router &$router) {
  $userController = new UserController();

  $router->post("/login", [
    fn (Request $request, Response $response) => $userController->signIn($request, $response)
  ]);

  $router->post("/register", [
    fn (Request $request, Response $response) => $userController->signUp($request, $response)
  ]);

  $router->middlewares(["luthier:jwt"])->put("/{id}", [
    fn (Request $request, Response $response, $id) => $userController->update($request, $response, $id)
  ]);

  $router->get("/logout", [
    fn (Request $request, Response $response) => $userController->signOut($request, $response)
  ]);

  $router->middlewares(["luthier:jwt"])->get("/", [
    fn (Request $request, Response $response) => $userController->findAll($request, $response)
  ]);

  $router->middlewares(['luthier:jwt'])->is([])->get("/{id}", [
    fn (Request $request, Response $response, $id) => $userController->findOne($request, $response, $id)
  ]);
});
