<?php
// Prossegue com a execução do instalador
require_once __DIR__ . "/../../autoload.php";

use Luthier\Cli\Colors\Colors;
use Luthier\Cli\Cli;
use Luthier\Cli\Output;

// Mensagem inicial
Output::separator("#", [Colors::$textWhite, Colors::$backgroundLightMagenta]);
$changelog = __DIR__ . "/CHANGELOG.md";
$changelogFirstLine = fgets(fopen($changelog, 'r'));
$colors = [Colors::$textLightYellow, Colors::$backgroundBlack];
$versioColors = [Colors::$textLightGreen, Colors::$backgroundBlack];
$startMessage = <<<LUTHIER

.--d8b,
(  '`YP)   ┏┓╋╋┏┓┏┓┏┓
\ f||? /   ┃┃┏┳┫┗┫┗╋╋━┳┳┓
/ j||t \   ┃┗┫┃┃┏┫┃┃┃┻┫┏┛
(  ||  )   ┗━┻━┻━┻┻┻┻━┻┛
 `-||-'    ┏━━┓╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋┏┓
   ||      ┃━┳╋┳┳━┓┏━━┳━┳┳┳┳━┳┳┫┣┓
   ||      ┃┏┛┃┏┫╋┗┫┃┃┃┻┫┃┃┃╋┃┏┫━┫
   ||      ┗┛╋┗┛┗━━┻┻┻┻━┻━━┻━┻┛┗┻┛
  =JJ=
LUTHIER;

$message = "OLÁ! SEJA BEM VINDO AO LUTHIER FRAMEWORK";
echo "\n";
Output::charByChar($message, 5, $colors);
Output::output($startMessage, "\n", $colors);
Output::output("V $changelogFirstLine", "\n", $colors);

// MENU - PROJECT SKELETON
$path = dirname(__DIR__, 4);
Output::charByChar("SELECIONE UMA OPÇÃO", 5, $colors);
Output::output("[1] - CRIAR ESQUELETO DO PROJETO NA PASTA ATUAL ($path)", "\n", $colors);
Output::output("[*] - SAIR SEM FAZER NADA", "\n", $colors);
$result = trim(readline(">>> "));

$basicProjectStructure = file_get_contents(__DIR__ . "/templates/basic/index.json");
$basicProjectStructureArray = json_decode($basicProjectStructure, associative: true);

$apiProjectStructure = file_get_contents(__DIR__ . "/templates/api/index.json");
$apiProjectStructureArray = json_decode($apiProjectStructure, associative: true);

// ~/vendor/dantas/luthier/start.php
$userProjectPath = dirname(__DIR__, 4);

$choose = match ($result) {
  "1" => (new Cli("basic"))->startProject("", $basicProjectStructureArray, $userProjectPath),
  "2" => (new Cli("api"))->startProject("", $apiProjectStructureArray, $userProjectPath),
  default => Cli::exitPrompt()
};

// MENU - TESTING FRAMEWORK
Output::charByChar("SELECIONE UM FRAMEWORK DE TESTES", 5, $colors);
Output::output("[1] - PEST (https://pestphp.com/)", "\n", $colors);
Output::output("[*] - POR PADRÃO INSTALARÁ O PEST", "\n", $colors);
$result = trim(readline(">>> "));

$choose = match ($result) {
  "1" => Cli::installTestFramework("pest"),
  default => Cli::installTestFramework("default")
};

Output::separator("#", [Colors::$textWhite, Colors::$backgroundLightMagenta]);
