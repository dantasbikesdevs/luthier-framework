<?php

declare(strict_types=1);

namespace Luthier\Utils;

class View
{
  /**
   * Método responsável por retornar o conteúdo do arquivo.
   */
  private static function getContentView($view)
  {
    $file = dirname(__DIR__, 5) . "/${view}.html";
    return file_exists($file) ? file_get_contents($file) : "";
  }

  /**
   * Método responsável por retornar o HTML gerado.
   */
  public static function render(string $view, array $params = [])
  {
    $contentView = self::getContentView($view);

    return self::html($contentView, $params);
  }

  /**
   * Método responsável por trocar as chaves pelos seus respectivos valores.
   */
  public static function html(string $htmlTemplate, array $vars = [])
  {
    $keys   = self::getKeys($vars);
    $values = array_values($vars);

    $values = array_map(function ($item) {
      if ($item && $item != '' || $item == '0') return $item;
      return '';
    }, $values);

    return str_replace($keys, $values, $htmlTemplate);
  }

  /**
   * Método responsável por retornar as chaves a serem mapeadas para a posterior
   * troca de valores.
   */
  private static function getKeys($vars)
  {
    $keys = array_keys($vars);

    $keys = array_map(function ($item) {
      return '{{' . $item . '}}';
    }, $keys);

    return $keys;
  }
}
