<?php

namespace Luthier\Cli;

use Luthier\Cli\Colors\Colors;

class Cli
{
  public function __construct($presetName)
  {
    require((__DIR__ . "/../../templates/$presetName/content.php"));
  }

  /**
   * Interpreta comando passados para este arquivo
   */
  public function startProject(string $templatePath, string $projectPath)
  {
    $errorColors = [Colors::$backgroundLightRed, Colors::$textRed];
    $initColors = [Colors::$backgroundCyan, Colors::$textWhite];
    $dividerColors = [Colors::$backgroundBlack, Colors::$textCyan];

    // Mensagem inicial
    Output::separator("-", $dividerColors);
    Output::charByChar(" GERANDO PROJETO ", 5, $initColors);

    // Cria a estrutura do projeto
    try {
      $this->create($templatePath, $projectPath);
    } catch (\Throwable $error) {
      Output::separator("-", $errorColors);
      $errorMessage = Colors::redText("Erro ao tentar gerar projeto :(");
      Output::center(Output::bold($errorMessage), "\n");
      echo Colors::yellowText($error);
    }

    // Instala dependências do projeto do usuário
    system("composer install");
    system("cd web-luthier-frame-v1; composer install");

    // Mensagem final
    Output::separator("=", $dividerColors);
  }

  # Diretório raiz do projeto do usuário
  private function create(string $templatePath, string $projectPath)
  {
    $dir = dir($templatePath);

    while ($file = $dir->read()) {
      if($file == "." || $file == "..") continue;
      $path = $templatePath . '/' . $file;
      $userPath = $projectPath . '/' . $file;
      if(is_dir($path)) {
        if (file_exists($userPath)) {
          echo Colors::redText("👎 PASTA JÁ EXISTE    👉 $userPath\n");
        } else {
          echo Colors::greenText("👌 PASTA FOI CRIADA   👉") . Colors::magentaText(" $userPath\n");
          mkdir($userPath);
          $this->create($path, $userPath);
        }
      } else {
        # Cria os arquivos
        $content = file_get_contents($path);

        if (file_exists($userPath)) {
          echo Colors::redText("👎 ARQUIVO JÁ EXISTE  👉 $userPath\n");
        } else {
          echo Colors::greenText("👌 ARQUIVO FOI CRIADO 👉") . Colors::cyanText(" $userPath\n");
          file_put_contents($userPath, $content ?? "");
        }
      }
    }
  }

  public static function exitPrompt()
  {
    $colors = [Colors::$textLightYellow, Colors::$backgroundBlack];
    Output::charByChar("ATÉ A PRÓXIMA", 5, $colors);
    exit;
  }

  public static function installTestFramework(string $framework)
  {
    echo match ($framework) {
      "pest" => InstallTest::pest(),
      "phppunit" =>  InstallTest::phpUnit(),
      "codeception" =>  InstallTest::codeception(),
      default => InstallTest::pest()
    };
  }
}
