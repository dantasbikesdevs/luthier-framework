<?php

namespace Luthier\Http\Middleware;

use Luthier\Http\Response;
use Luthier\Http\Request;

class Queue
{
  /**
   * Mapeamento de middlewares
   */
  private static array $map = [];

  /**
   * Mapeamento de middlewares padrões em todas rotas
   */
  private static array $default = [];

  /**
   * Fila de middleware a serem executados
   */
  private array $middlewares = [];

  /**
   * Função de execução do controller
   * @var callable
   */
  private $controller;

  /**
   * Argumentos da função do controller
   */
  private array $controllerArgs = [];

  /**
   * Método para construção do middleware
   */
  public function __construct(array $middlewares, callable $controller, array $controllerArgs)
  {
    $this->middlewares = array_merge(self::$default, $middlewares);
    $this->controller = $controller;
    $this->controllerArgs = $controllerArgs;
  }

  /**
   * Método responsável por setar o mapa de middlewares
   */
  public static function setMap(array $map)
  {
    self::$map = $map;
  }

  /**
   * Método responsável por setar o mapa de middlewares
   */
  public static function setDefault(array $default)
  {
    self::$default = $default;
  }


  /**
   * Método responsável por executar os middlewares e controllers
   */
  public function next(Request $request): Response
  {
    // VERIFICA SE A FILA DE MIDDLEWARES ESTÁ VAZIA
    if (empty($this->middlewares)) return call_user_func_array($this->controller, $this->controllerArgs);

    // MIDDLEWARE
    $middleware = array_shift($this->middlewares);

    if (!isset(self::$map[$middleware])) {
      $request->internalServerError('Problemas ao processar um middleware');
    }

    // NEXT
    $queue = $this;
    $next  = function ($request) use ($queue) {
      return $queue->next($request);
    };

    // EXECUTA O MIDDLEWARE
    return (new self::$map[$middleware])->handle($request, $next);
  }
}
