<?php

declare(strict_types=1);

namespace Luthier\Http\Router\Abstracts;

use Luthier\Exceptions\ParameterException;
use ReflectionFunction;
use ReflectionMethod;

abstract class Action
{
    /**
     * Método responsável por retornar os parâmetros do método
     * do controlador ou da closure.
     *
     * Deve retornar um array no seguinte modelo:
     * [ [ 'name' => 'id', 'type' => 'int' ] ]
     */
    protected function getParameters(ReflectionMethod|ReflectionFunction $reflection): array
    {
        $parameters = $reflection->getParameters();

        return array_map(function ($parameter) {
            return [
                "type" => $parameter->getType()?->getName() ?? "",
                "name" => $parameter->getName(),
            ];
        }, $parameters);
    }

    /**
     * Método responsável por retornar apenas os argumentos que
     * são parâmetros do método do controlador ou da closure.
     *
     * @throws ParameterException
     */
    protected function getArguments(array $parameters, array $variables): array
    {
        $numericPrimitiveTypes = ["int", "float"];

        $arguments = [];
        foreach ($parameters as $parameter) {
            $lowerCaseName = strtolower($parameter["name"]);

            $value = $variables[$lowerCaseName] ?? null;

            if (!isset($value)) continue;

            if (in_array($parameter["type"], $numericPrimitiveTypes) && ! is_numeric($value)) {
                throw new ParameterException(
                    "O valor do parâmetro {$parameter["name"]} da rota não é númerico.
                ");
            }

            $arguments[$parameter["name"]] = $value;
        }

        return $arguments;
    }
}
