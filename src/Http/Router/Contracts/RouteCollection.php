<?php

declare(strict_types=1);

namespace Luthier\Http\Router\Contracts;

use Luthier\Http\Router\Contracts\Route as RouteInterface;

interface RouteCollection
{
    /**
     * Método responsável por dicionar uma nova rota
     * a coleção de rotas.
     */
    public function add(RouteInterface $route): static;

    /**
     * Método responsável por verificar e retornar
     * se a coleção de rotas está vazia.
     */
    public function isEmpty(): bool;

    /**
     * Método responsável por retornar os métodos HTTP
     * das rotas da coleção.
     *
     * @return array<int, string>
     */
    public function getHttpMethods(): array;

    /**
     * Método responsável por retornar as rotas da coleção
     * que "baterem" com o pattern da URI requisitada.
     */
    public function getByUri(string $uri): static;

    /**
     * Método responsável por retornar as rotas da coleção
     * que possuirem o método HTTP requisitado.
     */
    public function getByHttpMethod(string $httpMethod): static;

    /**
     * Método responsável por retornar o primeiro elemento
     * da coleção de rotas.
     */
    public function first(): ?RouteInterface;

    /**
     * Método responsável por retornar todos os elementos
     * da coleção de rotas.
     *
     * @return array<int, RouteInterface>
     */
    public function all(): array;
}
