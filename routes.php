<?php

declare(strict_types=1);

use Luthier\Http\Router\Router;

Router::prefix("/test/opa")->middlewares(["api"])->group([
    Router::get("/users/{id}/{productId}"),
    Router::put("/users"),
    Router::delete("/users"),
]);

Router::run()->sendResponses();

