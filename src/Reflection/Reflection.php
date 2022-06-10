<?php

declare(strict_types=1);

namespace Luthier\Reflection;

class Reflection
{
  /**
   * Método responsável por retornar um array com todos os atributos do objeto,
   * exceto os que estiverem no atributo hidden.
   */
  public static function getValuesObject(object $object): array
  {
    $reflection = new \ReflectionClass($object);
    $properties = $reflection->getProperties();

    // Filtra e verifica se o atributo hidden existe no objeto
    $hidden = array_values(array_filter($properties, function ($property) {
      if ($property->getName() == "hidden") return true;
    }));

    // Se o atributo hidden existir, retorna um array com o valor do atributo
    $attributesHidden = empty($hidden) ? [] : $hidden[0]->getValue($object);
    $attributesHidden[] = "hidden";

    // Itera sobre todos os atributos do objeto e retorna um array com os valores
    foreach ($properties as $property) {
      $key = trim($property->getName());
      if (in_array($key, $attributesHidden) || !$property->isInitialized($object)) continue;
      $objectValues[$key] = $property->getValue($object);
    }

    return $objectValues;
  }
}
