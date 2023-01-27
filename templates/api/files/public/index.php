<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/config.php";

use Luthier\Http\Router\Router;
use Luthier\Utils\Path;

# Inicia o router (roteador)
$router = new Router();

$routes = Path::getMultipleFiles(PROJECT_ROOT . "/routes/v1");

foreach ($routes as $route) {
  include $route;
}

# Envia as respostas da rota
$router->run()
       ->sendResponses();

