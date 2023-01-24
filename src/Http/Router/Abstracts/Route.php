<?php

declare(strict_types=1);

namespace Luthier\Http\Router\Abstracts;

use Closure;
use Luthier\Exceptions\RouterException;
use Luthier\Http\Request;
use Luthier\Http\Response;
use Luthier\Http\Router\Contracts\Route as RouteInterface;
use Luthier\Http\Router\Callback;
use Luthier\Http\Router\Controller;

abstract class Route implements RouteInterface
{
    /**
     * Método HTTP da rota.
     */
    protected string $httpMethod;

    /**
     * Prefixo da rota.
     */
    protected string $prefix = "";

    /**
     * URI da rota.
     */
    protected string $uri = "";

    /**
     * Middlewares da rota.
     */
    protected array $middlewares = [];

    /**
     * Controlador da rota.
     */
    protected Action $controller;

    /**
     * Ação da rota, caso não seja passado o
     * controlador.
     */
    protected Action $closure;

    /**
     * Variáveis da rota.
     */
    protected array $variables = [];

    /**
     * Permissões de acesso a rota.
     */
    protected array $permissions = [];

    /**
     * Regras de acesso a rota.
     */
    protected array $rules = [];

    /**
     * Telas de acesso a rota.
     */
    protected array $screens = [];

    /**
     * Requisição atual.
     */
    protected static Request $request;

    public function __construct(
        string  $method,
        string  $uri,
        Request $request
    )
    {
        $this->httpMethod = $method;
        $this->setUri($uri);
        $this->controller = new Controller();
        self:: $request = $request;
    }

    /**
     * Método responsável por setar o prefixo da rota.
     */
    public function prefix(string $prefix): static
    {
        $this->prefix = str_starts_with($prefix, "/") ? $prefix : "/{$prefix}";

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
        if (empty($middlewares)) return $this;

        foreach ($middlewares as $middleware) {
            $this->addMiddleware($middleware);
        }

        return $this;
    }

    /**
     * Método responsável por adicionar um middleware a rota.
     */
    protected function addMiddleware(string $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Método responsável por setar as permissões da rota.
     *
     * @param array<int, string> $permissions
     */
    public function is(array $permissions): static
    {
        if (empty($permissions)) return $this;

        $this->permissions = $permissions;
        $this->middlewares(["auth", "is"]);

        return $this;
    }

    /**
     * Método responsável por setar as regras da rota.
     *
     * @param array<int, string> $rules
     */
    public function can(array $rules): static
    {
        if (empty($rules)) return $this;

        $this->rules = $rules;
        $this->middlewares(["auth", "can"]);

        return $this;
    }

    /**
     * Método responsável por setar as telas da rota.
     *
     * @param array<int, string> $screens
     */
    public function see(array $screens): static
    {
        if (empty($screens)) return $this;

        $this->screens = $screens;
        $this->middlewares(["auth", "screen"]);

        return $this;
    }

    /**
     * Método responsável por setar o controlador da rota.
     */
    public function controller(string $className, string $methodName = ""): static
    {
        $this->controller->setClassName($className);

        if (empty($methodName)) return $this;

        $this->controller->setMethodName($methodName);

        return $this;
    }

    /**
     * Método responsável por setar o método do controlador
     * da rota.
     */
    public function method(string $methodName): static
    {
        $this->controller->setMethodName($methodName);

        return $this;
    }

    /**
     * Método responsável por setar a ação da rota.
     * Utilizado para rotas que não possuem um controlador.
     */
    public function closure(callable $closure): static
    {
        $this->closure = new Callback($closure);

        return $this;
    }

    /**
     * Método responsável por setar a URI da rota já
     * com o pattern para verificação de rotas posteriormente.
     */
    protected function setUri(string $uri): void
    {
        $uri = str_starts_with($uri, "/") ? $uri : "/{$uri}";

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
        return array_unique($this->middlewares);
    }

    /**
     * Método responsável por retornar as permissões da rota.
     */
    public function getPermissions(): array
    {
        return array_unique($this->permissions);
    }

    /**
     * Método responsável por retornar as regras da rota.
     */
    public function getRules(): array
    {
        return array_unique($this->rules);
    }

    /**
     * Método responsável por retornar as telas da rota.
     */
    public function getScreens(): array
    {
        return array_unique($this->screens);
    }

    /**
     * Método responsável por retornar a closure da rota,
     * seja do controlador ou da função passada.
     */
    public function getAction(): ?Closure
    {
        if (! isset($this->controller) && ! isset($this->closure)) {
            throw new RouterException(
                "Nenhuma ação foi definida para esta rota."
            );
        }

        $arguments = $this->getVariables();

        return isset($this->closure) ?
            $this->closure->getClosure($arguments)
            : $this->controller->getClosure($arguments);
    }

    /**
     * Método responsável por retornar as variáveis da rota.
     */
    protected function getVariables(): array
    {
        $values = $this->getValueOfVariables();

        return array_merge([
            "request"  => self:: $request,
            "response" => new Response(),
        ], array_combine($this->variables, $values));
    }

    /**
     * Método responsável por retornar os valores das variáveis da rota.
     */
    protected function getValueOfVariables(): array
    {
        preg_match($this->getUri(), self::$request->getUri(), $variables);

        unset($variables[0]);

        return $variables;
    }

    /**
     * Método responsável por gerar o pattern da URI da rota
     * para que seja possível localizá-la em futuras requisições.
     */
    protected function patternUri(string $uri): string
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
