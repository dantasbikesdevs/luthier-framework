<?php

declare(strict_types=1);

namespace Luthier\Reflection;

class Reflection
{
  /**
   * Método responsável por retornar um array com todos os atributos do objeto,
   * exceto os que estiverem no atributo unusedInSQL.
   */
  public static function getValuesObjectToSQL(object $object): array
  {
    $reflection = new \ReflectionClass($object);
    $properties = $reflection->getProperties();

    if (empty($properties)) return self::getDynamicAttributes($object);

    $unusedInSQL = self::getAttributeValue($object, $properties, "unusedInSQL");

    // Itera sobre todos os atributos do objeto e retorna um array com os valores
    foreach ($properties as $property) {
      $key = trim($property->getName());
      if (in_array($key, $unusedInSQL) || !$property->isInitialized($object)) continue;
      $objectValues[$key] = $property->getValue($object);
    }

    return $objectValues;
  }

  /**
   * Método responsável por retornar um array com todos os atributos do objeto,
   * exceto os que estiverem no atributo hiddenAttributes.
   */
  public static function getValuesObjectToReturnUser(object $object): array
  {
    $reflection = new \ReflectionClass($object);
    $properties = $reflection->getProperties();

    if (empty($properties)) return self::getDynamicAttributes($object);

    $hiddenAttributes = self::getAttributeValue($object, $properties, "hiddenAttributes");

    // Itera sobre todos os atributos do objeto e retorna um array com os valores
    foreach ($properties as $property) {
      $key = trim($property->getName());
      if (in_array($key, $hiddenAttributes) || !$property->isInitialized($object)) continue;
      $objectValues[$key] = $property->getValue($object);
    }

    return $objectValues;
  }

  /**
   * Método responsável por retornar um array com todos os atributos do objeto.
   */
  public static function getValuesObject(object $object): array
  {
    $reflection = new \ReflectionClass($object);
    $properties = $reflection->getProperties();

    if(empty($properties)) return self::getDynamicAttributes($object);

    // Itera sobre todos os atributos do objeto e retorna um array com os valores
    foreach ($properties as $property) {
      if (!$property->isInitialized($object)) continue;
      $key = trim($property->getName());
      $objectValues[$key] = $property->getValue($object);
    }

    return $objectValues;
  }

  /**
   * Método responsável por retornar os atributos que devem ser escondidos do usuário ou ignorados no SQL, caso existam.
   */
  private static function getAttributeValue(object $object, array $properties, string $attribute): array
  {
    // Filtra e verifica se o atributo existe no objeto
    $filteredAttribute = array_values(array_filter($properties, function ($property) use ($attribute) {
      if ($property->getName() == $attribute) return true;
    }));

    // Se o atributo existir, retorna um array com o valor do atributo
    $attributeValue = empty($filteredAttribute) ? [] : $filteredAttribute[0]->getValue($object);
    $attributeValue[] = "hiddenAttributes";
    $attributeValue[] = "unusedInSQL";

    return $attributeValue;
  }

  /**
   * Método responsável por retornar os atributos dinâmicos de um objeto e seus valores, caso existam.
   */
  private static function getDynamicAttributes(object $object): array
  {
    if (empty($object)) return [];

    $attributes = [];
    foreach ($object as $key => $value) {
      $attributes[$key] = is_object($value) ? self::getDynamicAttributes($value) : $value;
    }

    return $attributes;
  }
}
