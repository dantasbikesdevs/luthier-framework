<?php

namespace Luthier\Utils;

use Exception;

class Transform
{
  public static function objectToArray(mixed $object): array
  {
    if (is_array($object)) return (array) $object;

    $type = gettype($object);

    if (!is_object($object)) throw new Exception("Essa função aceita apenas objetos e arrays como parâmetro, mas você passou um elemento do tipo $type");

    foreach ($object as $key => $value) {
      $array[$key] = $value;
    }
    return $array;
  }
}
