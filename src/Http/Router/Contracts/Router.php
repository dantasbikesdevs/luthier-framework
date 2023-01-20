<?php

declare(strict_types=1);

namespace Luthier\Http\Router\Contracts;

use Luthier\Http\Response;

interface Router
{
    /**
     * Método responsável por registrar uma nova rota GET
     * e retornar o seu registro para demais manipulações.
     */
    public static function get(string $uri): Route;

    /**
     * Método responsável por registrar uma nova rota POST
     * e retornar o seu registro para demais manipulações.
     */
    public static function post(string $uri): Route;

    /**
     * Método responsável por registrar uma nova rota GET
     * e retornar o seu registro para demais manipulações.
     */
    public static function put(string $uri): Route;

    /**
     * Método responsável por registrar uma nova rota PATCH
     * e retornar o seu registro para demais manipulações.
     */
    public static function patch(string $uri): Route;

    /**
     * Método responsável por registrar uma nova rota DELETE
     * e retornar o seu registro para demais manipulações.
     */
    public static function delete(string $uri): Route;

    /**
     * Método responsável por registrar um grupo de rotas e
     * retornar o seu registro para demais manipulações.
     */
    public static function prefix(string $prefix): Route;

    /**
     * Método responsável por executar o roteador, executando
     * assim a rota atual.
     */
    public static function run();
}
