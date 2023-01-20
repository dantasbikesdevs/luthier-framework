<?php

declare(strict_types=1);

namespace Luthier\Http\Router;

use InvalidArgumentException;
use Luthier\Http\Router\Contracts\Route as RouteInterface;
use Luthier\Http\Router\Contracts\Controller as ControllerInterface;

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
        // TODO: Implement getClosure() method.
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
