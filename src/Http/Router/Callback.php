<?php

namespace Luthier\Http\Router;

use Closure;

use Luthier\Http\Router\Abstracts\Action;
use ReflectionFunction;

class Callback extends Action
{
    /**
     * Rota que está sendo executada.
     */
    private Closure $closure;

    public function __construct(
        Closure $closure,
    )
    {
        $this->closure = $closure;
    }

    /**
     * Método responsável por retornar a closure da rota
     * para que seja seja executada com os seus devidos
     * parâmetros.
     */
    public function getClosure(array $variables): Closure
    {
        $closure = $this->closure;

        $reflection = new ReflectionFunction($closure);

        $parameters = $this->getParameters($reflection);

        $arguments = $this->getArguments($parameters, $variables);

        return fn () => $closure(...$arguments);
    }
}
