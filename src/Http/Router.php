<?php declare(strict_types=1);

namespace Luthier\Http;

use App\Http\ExceptionHandler;
use \Closure;
use \Exception;
use \ReflectionFunction;
use \Luthier\Http\Middlewares\Queue as Middleware;

class Router
{
  /**
   * URL completa do projeto (raiz)
   */
  private string $url = '';

  /**
   * Prefixo de todas as rotas
   */
  private string $prefix = '';

  /**
   * Índice de rotas
   */
  private array $routes = [];

  /**
   * Middlewares presentes no momento
   */
  private array $currentMiddlewares = [];

  /**
   * Instância de Request
   */
  private Request $request;

  /**
   * Instância de Response
   */
  private Response $response;

  /**
   * Tipo de conteúdo que está sendo retornado
   */
  private $contentType = 'application/json';

  /**
   * Regras da permissões
   */
  private array $permissions = [];

  /**
   * Regras da rota
   */
  private array $rules = [];

   /**
   * Regras de acesso a telas
   */
  private array $screens = [];

  /**
   * Método responsável por iniciar a classe
   */
  public function __construct(string $url)
  {
    $this->request = new Request($this);
    $this->response = new Response();
    $this->url = $url;
    $this->setPrefix();
  }

  // ! API PÚBLICA

  /**
   * Método para setar o tipo de conteúdo da rota atual
   */
  public function setContentType(string $contentType)
  {
    $this->contentType = $contentType;
  }

  /**
   * Configura os middlewares
   */
  public function middlewares(array $middlewares): self
  {
    foreach ($middlewares as $middleware) {
      array_push($this->currentMiddlewares, $middleware);
    }
    return $this;
  }

  /**
   * Grupo de rotas
   */
  public function group(string $prefix, callable $callback)
  {
    $middlewares = $this->getMiddlewares();
    $permissions = $this->permissions();
    $rules = $this->rules();
    $router = new Router('');
    $callback($router);

    foreach ($router->routes as $patternRoute => $methods) {
      foreach ($methods as $method => $params) {
        $middlewares && $this->middlewares($middlewares);
        $permissions && $this->is($permissions);
        $rules && $this->can($rules);
        $this->prepareParams($params);
        $endpoint = $prefix != '' ? $this->mergeUri($prefix, $patternRoute) : $patternRoute;
        $this->routes[$endpoint][$method] = $params;
      }
    }
  }

  /**
   * Regras de permissões do usuário
   */
  public function permissions()
  {
    $permissions = $this->permissions;
    $this->permissions = [];
    return $permissions;
  }

  /**
   * Regras de permissões da rota
   */
  public function rules()
  {
    $rules = $this->rules;
    $this->rules = [];
    return $rules;
  }

  /**
   * Regras de permissões da rota
   */
  public function screens()
  {
    $screens = $this->screens;
    $this->screens = [];
    return $screens;
  }

  /**
   * Adiciona verificação de permissão do usuário baseado em um conjunto de poderes (Ex: admin)
   */
  public function is(array $permissions): self
  {
    $this->middlewares(['auth', 'is']);
    $this->permissions = $permissions;
    return $this;
  }

  /**
   * Adiciona verificação de permissão do usuário para executar uma ação (Ex: apagar)
   */
  public function can(array $rules): self
  {
    $this->middlewares(['auth', 'can']);
    $this->rules = $rules;
    return $this;
  }

  /**
   * Adiciona verificação de permissão do usuário para acessar uma tela (Ex: marketing)
   */
  public function see(array $screens): self
  {
    $this->middlewares(['auth', 'see']);
    $this->screens = $screens;
    return $this;
  }

  public function prepareParams(&$params)
  {
    $middlewares = $this->getMiddlewares() ?? [];
    if (count($middlewares)) {
      $params['middlewares'] = $params['middlewares'] ?? [];
      $params['middlewares'] = array_merge($params['middlewares'], $middlewares);
    }

    $rules = $this->rules();
    if (count($rules)) {
      $params['rules'] = $params['rules'] ?? [];
      $params['rules'] = array_merge($params['rules'], $rules);
    }

    $permissions = $this->permissions();
    if (count($permissions)) {
      $params['permissions'] = $params['permissions'] ?? [];
      $params['permissions'] = array_merge($params['permissions'], $permissions);
    }

    $params['middlewares'] = isset($params['middlewares']) ? array_unique($params['middlewares']) : [];

    sort($params['middlewares']);

    $this->indexFirst($params['middlewares'], 'auth');
    $this->indexFirst($params['middlewares'], 'luthier:api');
  }

  /**
   * Move o item do array para o começo
   */
  public function indexFirst(&$array, $index)
  {
    if (!in_array($index, $array)) return;

    $newArray = [];
    foreach ($array as $key => $value) {
      if ($index == $value) {
        array_push($newArray, $value);
        unset($array[$key]);
      }
    }
    $array = array_merge($newArray, $array);
  }

  /**
   * Método responsável por definir uma rota no GET
   */
  public function get(string $route, $params = []): Router
  {
    $this->addRoute('GET', $route, $params);
    return $this;
  }

  /**
   * Método responsável por definir uma rota no POST
   */
  public function post(string $route, $params = []): Router
  {
    $this->addRoute('POST', $route, $params);
    return $this;
  }

  /**
   * Método responsável por definir uma rota no PUT
   */
  public function put(string $route, $params = []): Router
  {
    $this->addRoute('PUT', $route, $params);
    return $this;
  }

  /**
   * Método responsável por definir uma rota no DELETE
   */
  public function delete(string $route, $params = []): Router
  {
    $this->addRoute('DELETE', $route, $params);
    return $this;
  }

  /**
   * Método responsável por definir uma rota no DELETE
   */
  public function options(string $route, $params = []): Router
  {
    $this->addRoute('OPTIONS', $route, $params);
    return $this;
  }

  /**
   * Método responsável por retornar a URI sem prefixo.
   */
  public function getUri()
  {
    // URI DA REQUEST
    $uri = $this->request->getUri();

    // FATIA URI COM O PREFIXO
    $xUri = strlen($this->prefix) ? explode($this->prefix, $uri) : [$uri];

    // RETORNA A URI SEM PREFIXO
    return rtrim(end($xUri), '/');
  }

  /**
   * Retorna os parâmetros da rota atual
   */
  public function getParamsRoute($param)
  {
    // OBTÉM A ROTA ATUAL
    $route = $this->getRoute();
    return $route[$param] ?? null;
  }

  /**
   * Método responsável por executar a rota atual.
   */
  public function run(): Response
  {
    try {
      // Obtém a rota atual
      $route = $this->getRoute();

      // Verifica se tem um controlador
      if (!isset($route['controller'])) {
        throw new Exception('A URL não pode ser processada', 500);
      }

      // Argumentos do controlador
      $args = [];

      // Obtém os parâmetros do controller da rota e procura na lista de variáveis algo que corresponda aquilo
      $reflection = new ReflectionFunction($route['controller']);

      // Com getParameters temos acesso ao array de objetos de ReflectionParameter
      // Ex: fn($request, $response, $id) -> [ReflectionParameter, ReflectionParameter, ReflectionParameter]
      foreach ($reflection->getParameters() as $parameter) {

        // A partir de ReflectionParameter podemos obter o nome do parâmetro
        $name = $parameter->getName();
        // Criamos então o array de parâmetros do nosso controller ["request" => Request()]
        $args[$name] = $route['variables'][$name] ?? '';
      }

      // Cria o objeto da fila de middlewares
      $middlewareQueue = new Middleware(
        $route['middlewares'],
        $route['controller'],
        $args
      );

      // Retorna a execução da fila de middlewares
      $response = $middlewareQueue->next($this->request, $this->response);

      if ($response->getContent() instanceof Response) return $response->getContent();
      return $response;
    } catch (Exception $error) {
      if(class_exists(ExceptionHandler::class)) {
        return (new ExceptionHandler($error))->getResponse();
      }

      $httpCode = $error->getCode() == 0 ? 500 : $error->getCode();
      $errorMessage = $this->getErrorMessage($error, $httpCode);

      // Composição da resposta de erro
      $response = new Response();
      $response->setCode($httpCode);
      $response->setContent($errorMessage);
      $response->setContentType($this->contentType);
      return $response;
    }
  }

  /**
   * Retorna a mensagem de erro
   */
  public function getErrorMessage(Exception $error, int $httpCode)
  {
    return match ($this->contentType) {
      'application/json' => [
        'error'  => $error->getMessage()
      ],
      default => $error->getMessage()
    };
  }

  /**
   * Método responsável por retornar a url atual
   */
  public function getCurrentUrl()
  {
    return $this->url . $this->getUri();
  }

  /**
   * Método para redirecionar a página.
   */
  public function redirect(string $route)
  {
    $url = $this->url . $route;
    header("Location:" . $url);
    exit;
  }

  // ! MÉTODOS INTERNOS

  /**
   * Método responsável por retornar a URI desconsiderando o prefixo das rotas.
   */
  private function mergeUri(string $prefix, string $uri)
  {
    $initial = substr($uri, 0, 2);
    $uri = substr($uri, 3, strlen($uri));
    $prefix = str_replace('/', '\/', $prefix);
    if ($uri == "/") {
      return $initial . $prefix . "$/";
    }
    return $initial . $prefix . '\\' . $uri;
  }

  /**
   *  Método responsável por definir o prefixo das rotas
   */
  private function setPrefix()
  {
    // INFORMAÇÕES DA URL ATUAL
    $parseUrl = parse_url($this->url);

    // DEFINE O PREFIXO
    $this->prefix = $parseUrl['path'] ?? '';
  }

  /**
   * Middlewares
   */
  private function getMiddlewares(): ?array
  {
    $middlewares = $this->currentMiddlewares;
    $this->currentMiddlewares = [];
    return count($middlewares) ? $middlewares : null;
  }

  /**
   * Método responsável por adicionar uma rota na classe
   */
  private function addRoute(string $method, string $route, array $params = [])
  {

    $this->prepareParams($params);

    // VALIDAÇÃO DOS PARAMS
    foreach ($params as $key => $value) {
      if ($value instanceof Closure) {
        $params['controller'] = $value;
        unset($params[$key]);
        continue;
      }
    }

    // VARIÁVEIS DA ROTA
    $params['variables'] = [];

    // PADRÃO DE VALIDAÇÃO DAS VARIÁVEIS DAS ROTAS
    $patternVariable = '/{(.*?)}/';
    if (preg_match_all($patternVariable, $route, $matches)) {
      $route = preg_replace($patternVariable, '(.*?)', $route);
      $params['variables'] = $matches[1];
    }
    $route = rtrim($route, '/');

    // PADRÃO DE VALIDAÇÃO DA URL
    $patternRoute = '/^' . str_replace('/', '\/', $route) . '$/';

    // ADICIONA A ROTA DENTRO DA CLASSE
    $this->routes[$patternRoute][$method] = $params;
  }

  /**
   * Método responsável por retornar os dados da rota atual
   */
  private function getRoute(): array
  {
    // URI
    $uri = $this->getUri();

    // METHOD
    $httpMethod = $this->request->getHttpMethod();

    // VALIDA AS ROTAS
    foreach ($this->routes as $patternRoute => $methods) {
      // VERIFICA SE A URI BATE COM O PADRÃO
      if (preg_match($patternRoute, $uri, $matches)) {
        // VERIFICA O METHOD
        if (isset($methods[$httpMethod])) {
          // REMOVE A PRIMEIRA POSIÇÃO
          unset($matches[0]);

          // VARIÁVEIS PROCESSADAS
          $keys = $methods[$httpMethod]['variables'];
          $methods[$httpMethod]['variables'] = array_combine($keys, $matches);

          // Valores padrão para response e request
          $methods[$httpMethod]['variables']['request'] = $this->request;
          $methods[$httpMethod]['variables']['response'] = $this->response;

          // RETORNO DOS PARÂMETROS DA ROTA
          return $methods[$httpMethod];
        }

        // MÉTODO NÃO PERMITIDO PARA ESSA ROTA
        throw new Exception('Método não permitido', 405);
      }
    }
    // URL INEXISTENTE
    throw new Exception("Página não encontrada!", 404);
  }
}
