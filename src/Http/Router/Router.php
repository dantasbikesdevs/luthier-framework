<?php

namespace Luthier\Http\Router;

use Closure;
use Luthier\Exceptions\RouterException;
use Luthier\Http\Middlewares\Queue;
use Luthier\Http\Request;
use Luthier\Http\Response;
use Luthier\Http\Router\Contracts\Group as GroupInterface;
use Luthier\Http\Router\Contracts\RouteCollection as RouteCollectionInterface;
use Luthier\Http\Router\Contracts\Route as RouteInterface;
use Luthier\Http\Router\Contracts\Router as RouterInterface;

class Router implements RouterInterface
{
    /**
     * Prefixo global das rotas.
     */
    private static string $prefix;

    /**
     * Coleção de rotas do sistema.
     */
    private static RouteCollectionInterface $routes;

    /**
     * Rota atual.
     */
    private static RouteInterface $currentRoute;

    /**
     * Requisição atual.
     */
    private static Request $request;

    public function __construct(string $prefix = "")
    {
        self::$prefix = $prefix;
        self::$routes = new RouteCollection();
        self::$request = new Request($this);
    }

    /**
     * Método responsável por registrar uma nova rota GET
     * e retornar o seu registro para demais manipulações.
     */
    public static function get(string $uri, array|callable|string|null $action = null): RouteInterface
    {
        return self::addRoute("GET", $uri, $action);
    }

    /**
     * Método responsável por registrar uma nova rota POST
     * e retornar o seu registro para demais manipulações.
     */
    public static function post(string $uri, array|callable|string|null $action = null): RouteInterface
    {
        return self::addRoute("POST", $uri, $action);
    }

    /**
     * Método responsável por registrar uma nova rota PUT
     * e retornar o seu registro para demais manipulações.
     */
    public static function put(string $uri, array|callable|string|null $action = null): RouteInterface
    {
        return self::addRoute("PUT", $uri, $action);
    }

    /**
     * Método responsável por registrar uma nova rota PATCH
     * e retornar o seu registro para demais manipulações.
     */
    public static function patch(string $uri, array|callable|string|null $action = null): RouteInterface
    {
        return self::addRoute("PATCH", $uri, $action);
    }

    /**
     * Método responsável por registrar uma nova rota DELETE
     * e retornar o seu registro para demais manipulações.
     */
    public static function delete(string $uri, array|callable|string|null $action = null): RouteInterface
    {
        return self::addRoute("DELETE", $uri, $action);
    }

    /**
     * Método responsável por registrar um prefixo para um
     * grupo de rotas e retornar o seu registro para demais
     * manipulações.
     */
    public static function prefix(string $prefix): GroupInterface
    {
        return new Group($prefix, self::$request);
    }

    /**
     * Método responsável por adicionar uma nova rota ao coleção
     * de rotas do sistema.
     */
    private static function addRoute(
        string $method,
        string $uri,
        array|callable|string|null $action = null
    ): RouteInterface
    {
        $route = new Route($method, $uri, self::$request);
        $route->prefix(self::$prefix);

        if (is_array($action)) {
            $route->controller($action[0] ?? "", $action[1] ?? "");
        }

        if ($action instanceof Closure) {
            $route->closure($action);
        }

        if (is_string($action)) {
            $route->method($action);
        }

        self::$routes->add($route);

        return $route;
    }

    /**
     * Método responsável por retornar as rotas que "batem"
     * com a URI da requisição.
     */
    private static function getRoutesByUri(): RouteCollectionInterface
    {
        $uri = self::$request->getUri();

        $routes = self::$routes->getAllByUri($uri);

        if ($routes->isEmpty()) {
            throw new RouterException("Rota não encontrada", 404);
        }

        return $routes;
    }

    /**
     * Método responsável por retornar a rota atual.
     */
    public static function getCurrentRoute(): RouteInterface
    {
        return self::$currentRoute;
    }

    /**
     * Método responsável por executar o roteador, executando
     * assim a rota atual.
     */
    public static function run(): Response
    {
        $routes = self::getRoutesByUri();

        $httpMethod = self::$request->getHttpMethod();

        if ($httpMethod === Request::METHOD_OPTIONS) {
            return self::options($routes);
        }

        $route = $routes->getByHttpMethod($httpMethod)->first();

        if (!$route) {
            throw new RouterException("Método não permitido", 405);
        }

        self::$currentRoute = $route;

        $result = self::execute($route);

        return self::response($result);
    }

    /**
     * Método responsável por executar a rota.
     */
    private static function execute(RouteInterface $router): mixed
    {
        $closure = $router->getAction();

        $middlewareQueue = new Queue($router, $closure);

        return $middlewareQueue->execute(self::$request);
    }

    /**
     * Método responsável por retornar a resposta da requisição.
     */
    private static function response(mixed $content): Response
    {
        if ($content instanceof Response) return $content;

        return new Response($content);
    }

    /**
     * Método responsável por retornar uma resposta quando o
     * método for OPTIONS.
     */
    private static function options(RouteCollectionInterface $routes): Response
    {
        $methods = $routes->getHttpMethods();

        return new Response(null, 200, [
            "Access-Control-Allow-Methods" => implode(",", array_values($methods)),
        ]);
    }
}
