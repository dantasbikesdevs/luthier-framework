<?php

setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

use App\Database\ApplicationDatabase;
use App\Http\ExceptionHandler;
use Luthier\Database\DatabaseManager;
use Luthier\Environment\Environment;
use Luthier\Security\Jwt;
use Luthier\Http\Middlewares;
use Luthier\Log\Log;
use Luthier\Log\LogManager;

set_exception_handler(function ($error) {
  ExceptionHandler::init($error);
});

# Carrega as variáveis de ambiente presentes no arquivo .env na raiz
$envPath = __DIR__ . "/../.env";
$envObject = new Environment($envPath);
$envObject->load();

require_once __DIR__ . "/../database/config.php";
require_once __DIR__ . "/logging.php";

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
define('JWT_COOKIE_NAME', getenv("JWT_COOKIE_NAME"));

# Configura os canais de log
$logManager = new LogManager($channels);
Log::config($logManager);

# Configura a conexão com o banco de dados
$config = databaseConfig($env);
$databaseManager = new DatabaseManager($config);
ApplicationDatabase::init($databaseManager);

# Configuração do JWT
$signature = getenv("JWT_KEY");
Jwt::config($signature);

/**
 * Configuração dos Middlewares
 *
 * Por padrão, todos os middlewares do framework são mapeados e nomeados com o prefixo "luthier:" (luthier:api).
 * Caso você deseje adicionar novos middlewares personalizados, você deve passar o diretório onde eles estarão
 * armazenados (por padrão é no app/Middlewares). Eles serão nomeados seguindo o nome da classe definida, então
 * se a classe está nomeada como JwtAuth, o middleware deverá ser chamado na rota como jwtAuth, pois a primeira
 * letra é transformada em minúscula.
 *
 */
Middlewares::config("app/Http/Middlewares");

# Middlewares padrões (executados em todas as rotas)
Middlewares::setDefault([
  'luthier:api'
]);
