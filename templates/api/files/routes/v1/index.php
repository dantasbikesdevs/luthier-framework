<?php

use \Luthier\Http\Response;
use \Luthier\Http\Request;
use \Luthier\Http\Router;
use \App\Controller\UserController;

$userController = new UserController();

/**
 * Router existe aqui porque críamos este objeto em /public/index.php
 */
$router->group("/users", function (Router &$router) use ($userController) {
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

// Grupo
$router->is(["admin"])->group("/users", function (Router &$router) use ($userController) {
    $router->put("/{id}", [
        fn (Request $request, Response $response, $id) => $userController->update($request, $response, $id)
    ]);

    $router->get("/", [
        fn (Request $request, Response $response) => $userController->findAll($request, $response)
    ]);

    $router->get("/{id}", [
        fn (Request $request, Response $response, int $id) => $userController->findOne($request, $response, $id)
    ]);
});
