<?php

use Luthier\Http\Response;
use Luthier\Http\Request;

$router->middlewares(["jwtAuth"])->get("/", [
  function (Request $request, Response $response) {
    $response->body(["status" => "sucesso"])->ok();
  }
]);
