<?php

namespace Luthier\Utils;

use InvalidArgumentException;

class Validate {
  /**
   * Valida se os parâmetros recebidos estão setados corretamente.
   */
  public static function paramsRequired(array $params): void {
    foreach ($params as $key => $param) {
      if (isset($param) && (!empty($param) && $param != 0)) continue;
      throw new InvalidArgumentException("O parâmetro $key não foi definido.", 500);
    }
  }
}
