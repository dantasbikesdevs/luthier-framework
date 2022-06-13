<?php declare(strict_types=1);

namespace Luthier\Utils;

use Luthier\Reflection\Reflection;

class Transform
{
  public static function objectToArray(object $object): array
  {
    $array = Reflection::getValuesObjectToReturnUser($object);

    return $array;
  }
}
