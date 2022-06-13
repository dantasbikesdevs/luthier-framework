<?php declare(strict_types=1);

namespace Luthier\Utils;

use Exception;

class Transform
{
  public static function objectToArray(object $object): array
  {
    foreach ($object as $key => $value) {
      $array[$key] = $value;
    }
    
    return $array;
  }
}
