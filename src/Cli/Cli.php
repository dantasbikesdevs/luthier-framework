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
  public function startProject($folder, array $array, string $projectPath)
  {
    $errorColors = [Colors::$backgroundLightRed, Colors::$textRed];
    $initColors = [Colors::$backgroundCyan, Colors::$textWhite];
    $dividerColors = [Colors::$backgroundBlack, Colors::$textCyan];

    // Mensagem inicial
    Output::separator("-", $dividerColors);
    Output::charByChar(" GERANDO PROJETO ", 5, $initColors);

    // Cria a estrutura do projeto
    try {
      $this->create($folder, $array, $projectPath);
    } catch (\Throwable $error) {
      Output::separator("-", $errorColors);
      $errorMessage = Colors::redText("Erro ao tentar gerar projeto :(");
      Output::center(Output::bold($errorMessage), "\n");
      echo Colors::yellowText($error);
    }

    // Instala dependências do projeto do usuário
    system("composer install");
    system("cd LuthierFramework; composer install");

    // Mensagem final
    Output::separator("=", $dividerColors);
  }

  # Diretório raiz do projeto do usuário
  private function create($folder, array $array, string $projectPath)
  {
    # Recebe um array de pastas e arquivos traduzidas de um JSON
    foreach ($array as $key => $value) {
      # Se value for um array significa que é uma pasta
      if (is_array($value) && $value !== null) {
        # Cria a pasta unindo o caminho anterior com o atual
        $dirPath = "$projectPath/$folder$key";

        # Cria a pasta apenas se o diretório não estiver tomado
        if (file_exists($dirPath) || is_dir($dirPath)) {
          echo Colors::redText("👎 PASTA JÁ EXISTE    👉 $dirPath\n");
        } else {
          echo Colors::greenText("👌 PASTA FOI CRIADA   👉") . Colors::magentaText(" $dirPath\n");
          mkdir($dirPath);
        }

        # Continua executando a função até encontrar um elemento sem itens
        if (count($value)) {
          self::create($folder . $key . "/", $value, $projectPath);
        }
      } else {
        # Caso não seja um array é por que é um arquivo
        $key = is_int($key) ? "" : $key;
        $filename = $value === null ? "" : "$value";
        $filePath = "$folder$key$filename";

        $content = getPresetContent($filePath);

        # Cria os arquivos
        if (file_exists($filePath) || is_dir($filePath)) {
          echo Colors::redText("👎 ARQUIVO JÁ EXISTE  👉 $filePath\n");
        } else {
          echo Colors::greenText("👌 ARQUIVO FOI CRIADO 👉") . Colors::cyanText(" $filePath\n");
          file_put_contents("$projectPath/$filePath", $content ?? "");
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
