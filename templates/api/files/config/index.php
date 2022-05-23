<?php
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . "/../database/config.php";

use Luthier\Database\Database;
use Luthier\Environment\Environment;
use Luthier\Http\Middleware\Queue as MiddlewareQueue;
use Luthier\Security\Jwt;

# Carrega as variáveis de ambiente presentes no arquivo .env na raiz
$envPath = __DIR__ . "/../.env";
$envObject = new Environment($envPath);
$envObject->load();

# Obtendo variável de ambiente que indica se estamos em desenvolvimento ou produção
$env = getenv("ENV");

# Configurações de CORs
$applicationUrl = getenv("APPLICATION_URL");
header("Access-Control-Allow-Origin: $applicationUrl");
header("Vary: Origin");
header("Access-Control-Allow-Headers: *");

# Constantes globais
define('LUTHIER_URL', getenv('URL'));
define('PROJECT_ROOT', __DIR__ . "/../");

# Configura a conexão com o banco de dados
$configArray = databaseConfig($env);

Database::config(
  $configArray["driver"],
  $configArray["host"],
  $configArray["path"],
  $configArray["user"],
  $configArray["password"],
);

// Configuração do JWT
$signature = getenv("JWT_KEY");
Jwt::config($signature);

/**
 * Mapa de middlewares
 *
 * A chave do array corresponde ao nome que será utilizado para se referenciar ao middleware nas rotas
 * O valor é a classe do middleware
 *
 * [
 *  "meu_middleware" => App\Middlewares\MeuMiddleware::class
 * ]
 */
MiddlewareQueue::setMap([
  'is' => Luthier\Http\Middleware\Is::class,
  'api' => Luthier\Http\Middleware\Api::class,
  'can' => Luthier\Http\Middleware\Can::class,
  'jwt' => Luthier\Http\Middleware\Jwt::class,
  'maintenance' => Luthier\Http\Middleware\Maintenance::class,
]);

# Middlewares padrões (executados em todas as rotas)
MiddlewareQueue::setDefault([
  'maintenance',
  'api'
]);
