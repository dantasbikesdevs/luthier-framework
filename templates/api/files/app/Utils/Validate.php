<?php

namespace App\Utils;

use Exception;

class Validate
{

  public static function email(mixed $email)
  {
    $message = "E-mail inválido. Um e-mail válido deve conter essa estrutura xxx@xxx.xxx";
    if (!$email) throw new Exception($message, 404);
  }

  public static function password(mixed $password)
  {
    $message = "Senha inválido. Uma senha válida deve conter ao menos oito caracteres";
    if (!$password) throw new Exception($message, 404);
    if (strlen($password) < 8) throw new Exception($message, 404);
  }

  public static function notEmpty(mixed $value)
  {
    $message = "Este campo não pode estar vazio.";
    if (!$value) throw new Exception($message, 404);
  }
}
