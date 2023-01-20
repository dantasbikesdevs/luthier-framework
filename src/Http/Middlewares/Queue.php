<?php declare(strict_types=1);

namespace Luthier\Http\Middlewares;

use Exception;
use Luthier\Http\Response;
use Luthier\Http\Request;
use Luthier\Http\Router\Contracts\Route as RouteInterface;

class Queue
{
    /**
     * Middlewares da aplicação.
     * @var array<string, string>
     */
    private static array $middlewares = [];

    /**
     * Middlewares que executarão em todas as rotas.
     * @var array<string, string>
     */
    private static array $middlewaresDefaults = [];

    /**
     * Rota atual.
     */
    private RouteInterface $router;

    /**
     * @var callable
     */
    private $controller;

    public function __construct(RouteInterface $router, callable $controller)
    {
        $this->router = $router;
        $this->controller = $controller;
    }

    /**
     * Método responsável por cadastrar os middlewares da aplicação.
     */
    public static function map(array $middlewares): void
    {
        foreach ($middlewares as $name => $middleware) {
            self::$middlewares[$name] = $middleware;
        }
    }

    /**
     * Método responsável por adicionar os middlewares padrões.
     */
    public static function addDefaults(array $middlewares): void
    {
        foreach ($middlewares as $key => $middleware) {
            self::$middlewaresDefaults[$key] = $middleware;
        }
    }

    /**
     * Método responsável por retornar todos os middlewares da fila.
     */
    public function middlewares(): array
    {
        $routerMiddlewares = $this->router->getMiddlewares();

        return array_merge(self::$middlewaresDefaults, $routerMiddlewares);
    }

    /**
     * Método responsável por executar os middlewares.
     */
    public function execute(Request $request): mixed
    {
        $middlewares = $this->middlewares();

        return $this->next($request, $middlewares);
    }

    /**
     * Método responsável por executar o middleware.
     */
    private function next(Request $request, array &$middlewares): mixed
    {
        if (empty($middlewares)) return call_user_func($this->controller);

        $middleware = array_shift($middlewares);

        if (!isset(self::$middlewares[$middleware])) {
            throw new Exception("Middleware {$middleware} não encontrado.");
        }

        $queue = $this;
        $next = function (Request $request) use ($queue, $middlewares) {
            return $queue->next($request, $middlewares);
        };

        $currentMiddleware = new self::$middlewares[$middleware];

        if (!$currentMiddleware instanceof IMiddleware) {
            throw new Exception("Middleware não implementa a interface Middleware.");
        }

        return $currentMiddleware->handle($request, new Response(), $next);
    }
}
