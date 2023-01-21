<?php

declare(strict_types=1);

namespace Luthier\Http\Router\Contracts;

use Luthier\Http\Router\Contracts\Route as RouteInterface;

interface Route
{
    /**
     * Método responsável por modificar as rotas do grupo
     * com base na rota "pai". Neste método, o prefixo e os
     * middlewares da rota base são setados nas rotas do grupo.
     *
     * @param array<int, RouteInterface> $routes
     */
    public function group(array $routes): void;

    /**
     * Método responsável por setar o prefixo da rota.
     */
    public function prefix(string $prefix): static;

    /**
     * Método responsável por setar o controlador da rota.
     */
    public function controller(string $className, string $methodName): static;

    /**
     * Método responsável por setar a closure da rota.
     * Utilizado para rotas que não possuem um controlador.
     */
    public function closure(callable $closure): static;

    /**
     * Método responsável por adicionar os middlewares da rota.
     *
     * @param array<int, string> $middlewares
     */
    public function middlewares(array $middlewares): static;

    /**
     * Método responsável por retornar o prefixo da rota.
     */
    public function getPrefix(): string;

    /**
     * Método responsável por retornar o URI da rota.
     */
    public function getUri(): string;

    /**
     * Método responsável por retornar o método HTTP da rota.
     */
    public function getHttpMethod(): string;

    /**
     * Método responsável por retornar os middlewares da rota.
     */
    public function getMiddlewares(): array;
}
