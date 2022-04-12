<?php

namespace Luthier\Security;

use Exception;
use Firebase\JWT\JWT as FirebaseJwt;
use Firebase\JWT\Key;
use Luthier\Regex\Regex;

class Jwt
{
  /**
   * Assinatura usada para assinar e validar JWTs
   */
  private static $signature;

  private function __construct()
  {
  }

  /**
   * Recebe uma assinatura segura e configura o serviço JWT para usá-la
   */
  public static function config(string $signature)
  {
    self::$signature = self::validateSignature($signature);
  }

  /**
   * Recebe o conteúdo do payload como um array associativo e assina o JWT
   * com a chave configurada no método Jwt::config.
   */
  public static function encode(array $payload)
  {
    try {
      return FirebaseJwt::encode($payload, self::$signature, 'HS256');
    } catch (\Throwable $error) {
      throw $error;
    }
  }

  /**
   * Valida um JWT com base no seu conteúdo e a chave original com a que foi assinado ($signature).
   * Retorna o conteúdo do payload como um array associativo.
   */
  public static function decode(string $jwt)
  {
    try {
      $decodedPayloadObject = FirebaseJwt::decode($jwt, new Key(self::$signature, 'HS256'));
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
