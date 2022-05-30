<?php

namespace Luthier\Http\Middlewares;

use Exception;
use Luthier\Http\Middlewares\IMiddleware;
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
  public function next(Request $request, Response $response): Response
  {
    // Se não tivermos middlewares na fila então podemos pular tudo e executar o controller
    if (empty($this->middlewares)) return call_user_func_array($this->controller, $this->controllerArgs);

    // Pega o primeiro elemento da lista e o remove ao mesmo tempo (array_shift tem efeitos colaterais por usar a lista como referência)
    $middleware = array_shift($this->middlewares);

    // Se o middleware passado não estiver presente na lista de middlewares criada com a função setMap nós retornamos um erro
    if (!isset(self::$map[$middleware])) {
      return $response->internalServerError('Problemas ao processar um middleware');
    }

    // A fila recebe a atual instância deste objeto (reutilizando as propriedades)
    $queue = $this;

    /**
     * A função next usa o contexto do atual objeto de fila e chama o método queue até o momento em que algo interrompe o processo.
     * - Consumir todos os elementos da lista de middlewares interrompe o processo
     * - Não encontrar um middleware interrompe o processo
     * - Não encontrar a função handle em um middleware interrompe o processo
     */
    $next  = function ($request, $response) use ($queue) {
      return $queue->next($request, $response);
    };

    // Cria um objeto com a classe que corresponde ao middleware cadastrado no $map
    $actualMiddleware = new self::$map[$middleware];

    // Executa o middleware apenas de ele implementar IMiddleware
    if ($actualMiddleware instanceof IMiddleware) {
      return $actualMiddleware->handle($request, $response, $next);
    }

    // Executado apenas quando o middleware não implementa a interface desejada
    throw new Exception("Todos os middlewares devem implementar IMiddleware. Erro acontecendo ao interpretar middleware $middleware", 500);
  }
}
