<?php

namespace Luthier\Http\Router;

use InvalidArgumentException;
use Luthier\Http\Router\Contracts\Route as RouteInterface;
use Luthier\Http\Router\Contracts\Controller as ControllerInterface;
use ReflectionClass;
use ReflectionMethod;

class Controller implements ControllerInterface
{
    /**
     * Classe do controlador.
     */
    private string $className;

    /**
     * Método da classe do controlador.
     */
    private string $methodName;

    /**
     * Rota que utiliza este controlador.
     */
    private RouteInterface $route;

    public function __construct(
        string $className,
        string $methodName,
        Route $route
    )
    {
        $this->setClassName($className);
        $this->setMethodName($methodName);
        $this->route = $route;
    }

    public function getClosure()
    {
        $method = $this->methodName;

        $reflection = new ReflectionClass($this->className);

        $arguments = $this->getArguments();

        return fn () => $reflection
            ->newInstance()
            ->$method(...$arguments);
    }

    /**
     * Método responsável por retornar os parâmetros do método do controlador.
     */
    public function getParameters(): array
    {
        $reflection = new ReflectionMethod($this->className, $this->methodName);

        $parameters = $reflection->getParameters();

        return array_map(function ($parameter) {
            return $parameter->getName();
        }, $parameters);
    }

    /**
     * Método responsável por retornar os argumentos do método com os seus valores.
     */
    public function getArguments(): array
    {
        $parameters = $this->getParameters();

        $variables = $this->route->getVariables();

        $arguments = [];
        foreach ($parameters as $parameter) {
            $lowerCaseName = strtolower($parameter);

            if (!isset($variables[$lowerCaseName])) continue;

            $arguments[$parameter] = $variables[$lowerCaseName];
        }

        return $arguments;
    }

    /**
     * Método responsável por setar o nome da classe do controlador,
     * verificando antes se a classe existe.
     */
    private function setClassName(string $className): void
    {
        if (! class_exists($className, true)) {
            throw new InvalidArgumentException("Classe {$className} não existe.");
        }

        $this->className = $className;
    }

    /**
     * Método responsável por setar o nome do método da classe do
     * controlador, vericando antes se o método existe na classe.
     */
    private function setMethodName(string $methodName): void
    {
        if (! method_exists($this->className, $methodName)) {
            throw new InvalidArgumentException(
                "Método {$methodName} não existe na classe {$this->className}.
            ");
        }

        $this->methodName = $methodName;
    }
}
