<?php

namespace Luthier\Http;

use Exception;

class Cookie
{
  // O objetivo é que essa classe seja estática então não faz sentido ter um construtor
  private function __construct()
  {
  }

  /**
   * Método responsável por adicionar cookies a resposta. Quantidade + ".s" | ".min" | ".h" | ".d". Ex: "30.min" = 30 minutos
   */
  public static function send(array $cookie, string $duration = "1.min", string $path = "/", bool $secure = true, bool $httpOnly = true,  string $domain = "")
  {
    if (sizeof($cookie) > 1) throw new Exception("Apenas um cookie pode ser passado para esta função ['nome' => 'valor']");

    // Pega uma notação de data "10.d" e converte em timestamps Unix
    [$number, $symbol] = preg_split("/./", $duration);

    $number = (int) $number;

    $base = time();

    $expires = match ($symbol) {
      "s" => $base + $number, // segundos (+ n)
      "min" => $base + $number * 60, // minutos (n * 60)
      "h" => $base + $number * 3600, // horas (n * 60 * 60)
      "d" => $base + $number * 86400, // dias (n * 60 * 60 * 24)
      default => 0
    };

    $domain = getenv("DOMAIN") ?? "";

    /**
     * ["cookie" => "valor"]
     */
    $name = key($cookie);
    $value = array_values($cookie)[0];

    setcookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);
  }

  /**
   * Anula um cookie ao enviar um com o mesmo nome e pouquíssima duração
   */
  public static function delete(string $cookieName, string $path = "/", bool $secure = true, bool $httpOnly = true,  string $domain = "")
  {
    $expires = time() + 1;

    $domain = $domain ?? getenv("DOMAIN") ?? "";

    setcookie($cookieName, "", $expires, $path, $domain, $secure, $httpOnly);
  }

  /**
   * Método responsável por retornar um cookies especifico
   */
  public static function getByName(string $cookieName)
  {
    $cookies = $_COOKIE ?? [];
    return $cookies[$cookieName];
  }

  /**
   * Método responsável por retornar todos os cookies disponíveis
   */
  public static function getAll(): array
  {
    $cookies = $_COOKIE ?? [];
    return $cookies;
  }
}
