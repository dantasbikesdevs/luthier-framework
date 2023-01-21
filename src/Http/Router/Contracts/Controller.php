<?php

declare(strict_types=1);

namespace Luthier\Http\Router\Contracts;

use Closure;

interface Controller
{
    /**
     * Método responsável por retornar a closure do controlador da rota
     * para que seja seja executada com os seus devidos parâmetros.
     */
    public function getClosure(array $parameters): Closure;
}
