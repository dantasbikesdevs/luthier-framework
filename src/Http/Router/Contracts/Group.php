<?php

declare(strict_types=1);

namespace Luthier\Http\Router\Contracts;

interface Group extends Route
{
    /**
     * Método responsável por modificar as rotas do grupo
     * com base no grupo "pai". Neste método, o prefixo, permissões
     * e os middlewares da rota base são setados nas rotas do grupo.
     *
     * @param array<int, Route> $routes
     */
    public function group(array $routes): void;
}
