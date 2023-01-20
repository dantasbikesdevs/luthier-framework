<?php

declare(strict_types=1);

namespace Luthier\Http\Router;

use Closure;
use Luthier\Exceptions\RouterException;
use Luthier\Http\Request;
use Luthier\Http\Router\Contracts\Route as RouteInterface;
use Luthier\Http\Router\Contracts\Controller as ControllerInterface;

class Route implements RouteInterface
{
    /**
     * Método HTTP da rota.
     */
    private string $httpMethod;

    /**
     * Prefixo da rota.
     */
    private string $prefix;

    /**
     * URI da rota.
     */
    private string $uri;

    /**
     * Middlewares da rota.
     */
    private array $middlewares;

    /**
     * Controlador da rota.
     */
    private ControllerInterface $controller;

    /**
     * Ação da rota, caso não seja passado o
     * controlador.
     */
    private ?Closure $action;

    /**
     * Variáveis da rota.
     */
    private array $variables;

    private static Request $request;

    public function __construct(
        string $method,
        string $uri,
        Request $request
    )
    {
        $this->httpMethod = $method;
        $this->prefix = "";
        $this->uri = "";
        $this->middlewares = [];
        $this->variables = [];
        $this->setUri($uri);
        self:: $request = $request;
    }

    /**
     * Método responsável por modificar as rotas do grupo
     * com base na rota "pai". Neste método, o prefixo e os
     * middlewares da rota base são setados nas rotas do grupo.
     *
     * @param array<int, RouteInterface> $routes
     */
    public function group(array $routes): void
    {
        foreach ($routes as $route) {
            $route->prefix($this->prefix);
            $route->middlewares($this->middlewares);
        }
    }

    /**
     * Método responsável por setar o prefixo da rota.
     */
    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;

        $patternPrefix = $this->patternUri($prefix);

        $this->uri = $patternPrefix . $this->uri;

        return $this;
    }

    /**
     * Método responsável por adicionar os middlewares da rota.
     *
     * @param array<int, string> $middlewares
     */
    public function middlewares(array $middlewares): static
    {
        foreach ($middlewares as $middleware) {
            $this->addMiddleware($middleware);
        }

        return $this;
    }

    /**
     * Método responsável por adicionar um middleware a rota.
     */
    private function addMiddleware(string $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Método responsável por setar o controlador da rota.
     */
    public function controller(string $className, $methodName): static
    {
        $this->controller = new Controller(
            $className,
            $methodName,
            $this
        );

        return $this;
    }

    /**
     * Método responsável por setar a ação da rota.
     * Utilizado para rotas que não possuem um controlador.
     */
    public function action(callable $action): static
    {
        $this->action = $action;

        return $this;
    }

    private function setUri(string $uri): void
    {
        $patternUri = $this->patternUri($uri);

        $this->uri = $patternUri . '\/$/ism';
    }

    /**
     * Método responsável por retornar o método HTTP da rota.
     */
    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    /**
     * Método responsável por retornar o prefixo da rota.
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Método responsável por retornar o URI da rota.
     */
    public function getUri(): string
    {
        return "/^" . $this->uri;
    }

    /**
     * Método responsável por retornar os middlewares da rota.
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Método responsável por retornar as variáveis da rota.
     */
    public function getVariables(): array
    {
        $values = $this->getValueOfVariables();

        return array_merge([
            "request" => self:: $request
        ], array_combine($this->variables, $values));
    }

    /**
     * Método responsável por retornar os valores das variáveis da rota.
     */
    private function getValueOfVariables(): array
    {
        preg_match($this->getUri(), self:: $request->getUri(), $variables);

        unset($variables[0]);

        return $variables;
    }

    /**
     * Método responsável por retornar a closure da rota,
     * seja do controlador ou da função passada.
     */
    public function getAction(): ?Closure
    {
        if (! isset($this->controller) && ! isset($this->action)) {
            throw new RouterException(
                "Nenhuma ação foi definida para esta rota."
            );
        }

        return isset($this->controller) ?
            $this->controller->getClosure()
            : $this->action;
    }

    /**
     * Método responsável por gerar o pattern da URI da rota
     * para que seja possível localizá-la em futuras requisições.
     */
    private function patternUri(string $uri): string
    {
        if (empty($uri)) return "";

        $uri = preg_replace("/\/\/+/", "/", $uri);

        // Padrão de validação das variáveis da rota
        // Exemplo: /users/{id}/posts/{post_id}
        $patternVariable = '/{(.*?)}/';
        if (preg_match_all($patternVariable, $uri, $matches)) {
            $uri = preg_replace($patternVariable, '([\w!@""#$%¨&*ç()`+=:.?,<>_{};\-\'\']*?)', $uri);
            $this->variables = array_map("mb_strtolower", $matches[1]);
        }

        $uri = rtrim($uri, '/');

        return str_replace('/', '\/', $uri);
    }
}
