<?php

class Path
{

  /**
   * Método que pega o arquivo de configurações
   */
  public static function config(string $filename)
  {
    $filename = str_replace('.', '/', $filename);
    return require __DIR__ . '/../config/' . $filename . '.php';
  }

  /**
   * Método que pega diversos arquivos de um path
   */
  function getMultipleFiles(string $path)
  {
    $firstPath  = glob($path . '/*.php');
    $secondPath = glob($path . '/*/*.php');
    $thirdPath  = glob($path . '/*/*/*.php');

    return array_merge($firstPath, $secondPath, $thirdPath);
  }

  /**
   * Método que retorna todos os conteúdos de um diretório
   */
  public static function getDirContents($dir, $filter = '', &$filesPath = [])
  {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
      $path = realpath($dir . DIRECTORY_SEPARATOR . $value);

      if (!is_dir($path)) {
        if (empty($filter) || preg_match($filter, $path)) $filesPath[] = $path;
      } elseif ($value != "." && $value != "..") {
        self::getDirContents($path, $filter, $filesPath);
      }
    }
    return $filesPath;
  }
}
