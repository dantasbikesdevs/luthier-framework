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

menu($colors);

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

function menu($colors)
{
  // MENU - PROJECT SKELETON
  $userProjectPath = dirname(__DIR__, 3);
  $templatesPath = dirname(__DIR__) . "/luthier/templates";

  Output::charByChar("SELECIONE UMA OPÇÃO", 5, $colors);
  $dir = dir($templatesPath);
  for ($i=1; $file = $dir->read() ; $i++) {
    if($file == "." || $file == "..") continue;
    Output::output("[$i] - CRIAR ESQUELETO DO PROJETO A PARTIR DO TEMPLATE ($file)", "\n", $colors);
    $templates[$i] = $file;
  }
  Output::output("[*] - SAIR SEM FAZER NADA", "\n", $colors);
  $result = trim(readline(">>> "));

  if(!isset($templates[$result])) Cli::exitPrompt();

  $templatePath = $templatesPath . DIRECTORY_SEPARATOR . $templates[$result] . "/files";

  (new Cli($templates[$result]))->startProject($templatePath, $userProjectPath);
}
