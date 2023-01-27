<?php

use App\Controller\UserController;
use Luthier\Http\Router\Router;

Router::prefix("/users")
  ->controller(UserController::class)
  ->group([
    Router::post("/login", "signIn"),

    Router::post("/register", "signUp"),

    Router::get("/logout", "signOut")
  ]);

Router::prefix("/users")
  ->is(["admin"])
  ->controller(UserController::class)
  ->group([
    Router::put("/{id}", "update"),

    Router::get("/", "findAll"),

    Router::get("/{id}", "findOne")
  ]);
