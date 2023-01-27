<?php

declare(strict_types=1);

namespace Luthier\Http\Router;

use Luthier\Http\Router\Contracts\Route as RouteInterface;
use Luthier\Http\Router\Contracts\RouteCollection as RouteCollectionInterface;

class RouteCollection implements RouteCollectionInterface
{
    /**
     * Array com todas as rotas.
     *
     * @var array<int, Route>
     */
    private array $routes;

    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
    }

    /**
     * Método responsável por dicionar uma nova rota
     * a coleção de rotas.
     */
    public function add(RouteInterface $route): static
    {
        $this->routes[] = $route;

        return $this;
    }

    /**
     * Método responsável por verificar e retornar
     * se a coleção de rotas está vazia.
     */
    public function isEmpty(): bool
    {
        return empty($this->routes);
    }

    /**
     * Método responsável por retornar os métodos HTTP
     * das rotas da coleção.
     */
    public function getHttpMethods(): array
    {
        return array_unique(array_map(function (RouteInterface $route) {
            return $route->getHttpMethod();
        }, $this->routes));
    }

    /**
     * Método responsável por retornar as rotas da coleção
     * que "baterem" com o pattern da URI requisitada.
     */
    public function getByUri(string $uri = ""): static
    {
        $routes = array_values(array_filter($this->routes, function ($route) use ($uri) {
            return preg_match($route->getUri(), $uri);
        }));

        $staticRoutes = array_values(array_filter($routes, function ($route) {
            return ! $route->isDynamic();
        }));

        return new static(! empty($staticRoutes)
            ? array_merge($staticRoutes, $routes)
            : $routes
        );
    }

    /**
     * Método responsável por retornar as rotas da coleção
     * que possuirem o método HTTP requisitado.
     */
    public function getByHttpMethod(string $httpMethod): static
    {
        $routes = array_values(array_filter($this->routes, function ($route) use ($httpMethod) {
            return $route->getHttpMethod() === $httpMethod;
        }));

        return new static($routes);
    }

    /**
     * Método responsável por retornar o primeiro elemento
     * da coleção de rotas.
     */
    public function first(): ?RouteInterface
    {
        return $this->routes[0] ?? null;
    }

    /**
     * Método responsável por retornar todos os elementos
     * da coleção de rotas.
     */
    public function all(): array
    {
        return $this->routes;
    }
}
