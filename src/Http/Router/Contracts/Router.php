<?php

declare(strict_types=1);

namespace Luthier\Http\Router\Contracts;

use Luthier\Http\Router\Contracts\Route as RouteInterface;

interface Router
{
    /**
     * Método responsável por registrar uma nova rota GET
     * e retornar o seu registro para demais manipulações.
     */
    public static function get(string $uri): RouteInterface;

    /**
     * Método responsável por registrar uma nova rota POST
     * e retornar o seu registro para demais manipulações.
     */
    public static function post(string $uri): RouteInterface;

    /**
     * Método responsável por registrar uma nova rota GET
     * e retornar o seu registro para demais manipulações.
     */
    public static function put(string $uri): RouteInterface;

    /**
     * Método responsável por registrar uma nova rota PATCH
     * e retornar o seu registro para demais manipulações.
     */
    public static function patch(string $uri): RouteInterface;

    /**
     * Método responsável por registrar uma nova rota DELETE
     * e retornar o seu registro para demais manipulações.
     */
    public static function delete(string $uri): RouteInterface;

    /**
     * Método responsável por registrar um grupo de rotas e
     * retornar o seu registro para demais manipulações.
     */
    public static function prefix(string $prefix): RouteInterface;

    /**
     * Método responsável por retornar a rota atual.
     */
    public static function getRoute(): RouteInterface;

    /**
     * Método responsável por executar o roteador, executando
     * assim a rota atual.
     */
    public static function run();
}
