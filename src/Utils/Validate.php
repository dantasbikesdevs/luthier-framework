<?php

namespace Luthier\Utils;

use InvalidArgumentException;

class Validate {
  /**
   * Valida se os parâmetros recebidos estão setados corretamente.
   */
  public static function paramsRequired(array $params, int $code = 400): void {
    foreach ($params as $key => $param) {
      if (isset($param) && (!empty($param) && $param != 0)) continue;
      throw new InvalidArgumentException("O parâmetro $key não foi definido.", $code);
    }
  }
}
