<?php

namespace Luthier\JwtService;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Luthier\Regex\Regex;

class JwtService
{
  private function __construct()
  {
  }

  /**
   * Recebe o conteúdo do payload como um array associativo e assina o JWT com uma chave segura passada
   * como $signature.
   */
  public static function encode(array $payload, string $signature)
  {
    try {
      $validSignature = self::validateSignature($signature);
      return JWT::encode($payload, $validSignature, 'HS256');
    } catch (\Throwable $error) {
      throw $error;
    }
  }

  /**
   * Valida um JWT com base no seu conteúdo e a chave original com a que foi assinado ($signature).
   * Retorna o conteúdo do payload como um array associativo.
   */
  public static function decode(string $jwt, string $signature)
  {
    try {
      $decodedPayloadObject = JWT::decode($jwt, new Key($signature, 'HS256'));
      return (array) $decodedPayloadObject;
    } catch (\Throwable $error) {
      throw $error;
    }
  }

  private static function validateSignature(string $signature)
  {
    $errorMessages = [
      "fragile" => "Assinatura muito fraca. A assinatura deve ter no mínimo um símbolo ($*&@#), uma letra maiúscula e um número.",
      "tooShort" => "Assinatura muito pequena. Cria uma maior e mais forte.",
    ];

    if (strlen($signature) < 32) throw new Exception($errorMessages["fragile"]);
    if (!preg_match(Regex::$strongSignature, $signature)) throw new Exception($errorMessages["tooShort"]);

    return $signature;
  }
}
