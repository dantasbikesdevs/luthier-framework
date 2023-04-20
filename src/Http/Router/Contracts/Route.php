<?php

declare(strict_types=1);

namespace Luthier\Http\Router\Contracts;

interface Route
{
    /**
     * Método responsável por adicionar os middlewares da rota.
     *
     * @param array<int, string> $middlewares
     */
    public function middlewares(array $middlewares): static;

    /**
     * Método responsável por setar as permissões da rota.
     */
    public function is(array $permissions): static;

    /**
     * Método responsável por setar as permissões da rota.
     */
    public function can(array $rules): static;

    /**
     * Método responsável por setar as permissões da rota.
     */
    public function see(array $screens): static;

    /**
     * Método responsável por setar o prefixo da rota.
     */
    public function prefix(string $prefix): static;

    /**
     * Método responsável por setar o controlador da rota.
     */
    public function controller(string $className, string $methodName = ""): static;

    /**
     * Método responsável por setar o método do controlador
     * da rota.
     */
    public function method(string $methodName): static;

    /**
     * Método responsável por setar a closure da rota.
     * Utilizado para rotas que não possuem um controlador.
     */
    public function closure(callable $closure): static;

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

    /**
     * Método responsável por retornar as permissões da rota.
     */
    public function getPermissions(): array;

    /**
     * Método responsável por retornar as regras da rota.
     */
    public function getRules(): array;

    /**
     * Método responsável por retornar as telas da rota.
     */
    public function getScreens(): array;
}
