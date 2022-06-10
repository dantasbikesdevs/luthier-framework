<?php declare(strict_types=1);

namespace Luthier\Reflection;

class Reflection
{
  /**
   * Método responsável por retornar um array com todos os atributos do objeto.
   */
  public static function getValuesObject(object $object): array
  {
    $reflection = new \ReflectionClass($object);
    $properties = $reflection->getProperties();
    foreach ($properties as $property) {
      $property->setAccessible(true);
      $key = $property->getName();
      $objectValues[$key] = $property->getValue($object);
    }

    return $objectValues;
  }

  /**
   * Método responsável por retornar um array com os atributos do objeto que não
   * são readonly.
   */
  public static function getValuesObjectNoReadOnly(object $object)
  {
    $reflection = new \ReflectionClass($object);
    $properties = $reflection->getProperties();
    foreach ($properties as $property) {
      if ($property->isReadOnly()) continue;
      $property->setAccessible(true);
      $key = $property->getName();
      $objectValues[$key] = $property->getValue($object);
    }

    return $objectValues;
  }
}
