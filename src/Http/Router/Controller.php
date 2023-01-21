<?php

namespace Luthier\Http\Router;

use Closure;
use InvalidArgumentException;
use Luthier\Http\Router\Abstracts\Action;
use Luthier\Http\Router\Contracts\Controller as ControllerInterface;
use Reflection;
use ReflectionClass;
use ReflectionMethod;

class Controller extends Action implements ControllerInterface
{
    /**
     * Classe do controlador.
     */
    private string $className;

    /**
     * Método da classe do controlador.
     */
    private string $methodName;

    public function __construct(
        string $className,
        string $methodName
    )
    {
        $this->setClassName($className);
        $this->setMethodName($methodName);
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

    /**
     * Método responsável por retornar a closure do controlador da rota
     * para que seja seja executada com os seus devidos parâmetros.
     */
    public function getClosure(array $variables): Closure
    {
        $reflection = new ReflectionClass($this->className);

        $reflectionMethod = new ReflectionMethod($this->className, $this->methodName);

        $method = $this->methodName;

        $parameters = $this->getParameters($reflectionMethod);

        $arguments = $this->getArguments($parameters, $variables);

        return fn() => $reflection
            ->newInstance()
            ->$method(...$arguments);
    }
}
