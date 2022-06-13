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

    // Filtra e verifica se o atributo hidden existe no objeto
    $unused = array_values(array_filter($properties, function ($property) {
      if ($property->getName() == "unusedInSQL") return true;
    }));

    // Se o atributo hidden existir, retorna um array com o valor do atributo
    $unusedInSQL = empty($unused) ? [] : $unused[0]->getValue($object);
    $hiddenAttributes[] = "hiddenAttributes";
    $hiddenAttributes[] = "unusedInSQL";

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

    // Filtra e verifica se o atributo hidden existe no objeto
    $hidden = array_values(array_filter($properties, function ($property) {
      if ($property->getName() == "hiddenAttributes") return true;
    }));

    // Se o atributo hidden existir, retorna um array com o valor do atributo
    $hiddenAttributes = empty($hidden) ? [] : $hidden[0]->getValue($object);
    $hiddenAttributes[] = "hiddenAttributes";
    $hiddenAttributes[] = "unusedInSQL";

    $toLower = getenv("LOWER_CASE_RETURN") == "true" ? true : false;

    // Itera sobre todos os atributos do objeto e retorna um array com os valores
    foreach ($properties as $property) {
      $key = $property->getName();
      if (in_array($key, $hiddenAttributes) || !$property->isInitialized($object)) continue;
      $key = $toLower ? strtolower((string)$key) : $key;
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

    // Itera sobre todos os atributos do objeto e retorna um array com os valores
    foreach ($properties as $property) {
      $key = trim($property->getName());
      $objectValues[$key] = $property->getValue($object);
    }

    return $objectValues;
  }
}
