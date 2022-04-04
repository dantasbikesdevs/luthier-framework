<?php

namespace Luthier\Regex;

class Regex
{
  /**
   * A expressão passa se o texto passado possuir ao menos oito caracteres, um símbolo ($*&@#), uma letra maiúscula e uma letra minúscula
   * e se não houver caracteres subsequentes
   */
  public static $strongPassword = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[$*&@#])(?:([0-9a-zA-Z$*&@#])(?!\1)){8,}$/';

  /**
   * A expressão passa se o texto passado possuir ao menos trinta e dois caracteres, um símbolo ($*&@#), uma letra maiúscula e uma letra minúscula
   */
  public static $strongSignature = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[$*&@#])(?:([0-9a-zA-Z$*&@#])(?!\1)){32,}$/';

  /**
   * A expressão passa se o texto passado possuir a estrutura "xxx@xxx.xxx" não permitindo números após o "@"
   */
  public static $validEmail = '/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$\/';

  /**
   * A expressão passa se o texto passado possuir mais de um espaço subsequente
   */
  public static $contiguousBlankSpaces = '\/\s\s+/g';

  private function __construct()
  {
  }
}
