<?php

namespace Luthier\Http;

use Exception;
use Luthier\Http\Router;

class Request
{
  /**
   * Método HTTP da requisição
   */
  private string $httpMethod;

  /**
   * URI da página
   */
  private string $uri;

  /**
   * Tipo do conteúdo. Enviado como header Content Type
   */
  private string $contentType;

  /**
   * Variáveis recebidas por método HTTP GET ($_GET)
   */
  private array $queryParams = [];

  /**
   * Variáveis recebidas por método HTTP POST ($_POST)
   */
  private array $postVars = [];

  /**
   * Cookies da requisição
   */
  private array $cookies = [];

  /**
   * Informações do payload JWT
   */
  private array $payload = [];

  /**
   * Cabeçalhos da requisição
   */
  private array $headers = [];

  /**
   * Router da página
   */
  private Router $router;

  /**
   * Construtor da classe
   */
  public function __construct($router)
  {
    // Objeto da classe router que é injetado na requisição
    $this->router = $router;
    $this->queryParams = $_GET ?? [];
    $this->headers = getallheaders();
    $this->httpMethod = $_SERVER['REQUEST_METHOD'] ?? '';
    $this->cookies = $_COOKIE ?? [];
    $this->setUri();
    $this->setPostVars();
    $this->sanitize();
  }

  // ! API PÚBLICA

  /**
   * Método responsável por retornar o router
   */
  public function getRouter(): Router
  {
    return $this->router;
  }
  /**
   * Método responsável por retornar o método HTTP da requisição
   */
  public function getHttpMethod(): string
  {
    return $this->httpMethod;
  }

  /**
   * Método responsável por retornar a uri da requisição
   */
  public function getUri(): string
  {
    return $this->uri;
  }

  /**
   * Método responsável por retornar os Headers da requisição
   */
  public function getHeaders(): array
  {
    return $this->headers;
  }

  /**
   * Método responsável por retornar os parâmetros da URL($_GET) da requisição
   */
  public function getQueryParams(): array
  {
    return $this->queryParams;
  }

  /**
   *  Método responsável por retornar os parãmetros POST($_POST) da requisição
   */
  public function getPostVars($postValidated = null): array
  {
    if ($postValidated) {
      $this->validateParamsPost($postValidated);
    }
    return $this->postVars;
  }

  /**
   * Método responsável por retornar todos os cookies disponíveis
   */
  public function getAllCookies(): array
  {
    return $this->cookies;
  }

  /**
   * Método responsável por retornar um cookies especifico
   */
  public function getCookie(string $cookieName): ?string
  {
    return $this->cookies[$cookieName];
  }

  /**
   * Método responsável por definir informações do payload
   */
  public function setPayload(array $fields)
  {
    $this->payload = $fields;
  }

  /**
   * Método responsável por retornar informações do payload
   */
  public function getPayload(?string $field = null): mixed
  {
    if ($field) return $this->payload[$field];
    return $this->payload;
  }

  /**
   * Função responsável pelas requisições de download
   */
  public function download($size, $filename): self
  {
    return $this;
  }

  /**
   * Função responsável por configurar requisição para PDF
   */
  public function pdf(): self
  {
    $type = 'application/pdf';
    $this->getRouter()->setContentType($type);
    $this->contentType = $type;
    return $this;
  }

  /**
   * Função responsável por configurar requisição para JSON
   */
  public function json(): self
  {
    $type = 'application/json';
    $this->getRouter()->setContentType($type);
    $this->contentType = $type;
    return $this;
  }

  /**
   * Função responsável por configurar requisição para XML
   */
  public function xml(): self
  {
    $type = 'text/xml';
    $this->getRouter()->setContentType($type);
    $this->contentType = $type;
    return $this;
  }

  /**
   * Função responsável por enviar uma resposta ou erro
   */
  public function httpResponse(int $code, mixed $content): Response
  {
    $this->getRouter()->setContentType($this->contentType);
    if ($code >= 400) {
      throw new Exception($content, $code);
    }
    return new Response($code, $content, $this->contentType);
  }

  /**
   * Função responsável por indicar sucesso na requisição
   */
  public function ok(mixed $content)
  {
    return $this->httpResponse(200, $content);
  }

  /**
   * Função responsável por indicar sucesso na criação de conteúdo
   */
  public function created(mixed $content)
  {
    return $this->httpResponse(201, $content);
  }

  /**
   * Função responsável por indicar requisição não autorizada
   */
  public function unauthorized(string $content)
  {
    return $this->httpResponse(401, $content);
  }

  /**
   * Função responsável por indicar conflito na requisição
   */
  public function conflict(mixed $content)
  {
    return $this->httpResponse(409, $content);
  }

  /**
   * Função responsável por indicar que um conteúdo não foi encontrado
   */
  public function notFound(mixed $content)
  {
    return $this->httpResponse(404, $content);
  }

  /**
   * Função responsável por indicar erro no servidor
   */
  public function internalServerError(mixed $content)
  {
    return $this->httpResponse(500, $content);
  }

  /**
   * Função responsável por pegar as permissões do usuário
   */
  public function permissions()
  {
    return $this->router->getParamsRoute('permissions');
  }

  /**
   * Função responsável por pegar os papéis do usuário
   */
  public function roles()
  {
    return $this->router->getParamsRoute('roles');
  }

  /**
   * Método responsável por dizer que os parâmetros são obrigatórios
   */
  public function postRequired($postRequired): void
  {
    foreach ($postRequired as $key) {
      if (!$this->postVars[$key]) {
        throw new \Exception("O parâmetro ${key} é obrigatório!");
      }
    }
  }

  // ! MÉTODOS INTERNOS

  /**
   * Método responsável por definir as variáveis que vem no corpo da requisição
   */
  private function setPostVars()
  {
    $postVars = $_POST ?? [];
    $json = file_get_contents('php://input');
    $jsonVars = json_decode($json, true) ?? [];
    $this->postVars = array_merge($jsonVars, $postVars);
  }

  /**
   * Método responsável por definir a URI e por separar dos parâmetros GET
   */
  private function setUri()
  {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $xUri = explode('?', $uri);
    $this->uri = $xUri[0];
  }

  /**
   * Método responsável por limpar os dados do post, evitando injection, e deixando todos em UPPER
   */
  private function sanitize()
  {
    foreach ($this->postVars as $key => $value) {
      $cleanValue = $value;
      if (isset($cleanValue)) {
        $cleanValue = strip_tags(trim($cleanValue));
        $cleanValue = htmlentities($cleanValue, ENT_NOQUOTES);
        $cleanValue = html_entity_decode($cleanValue, ENT_NOQUOTES, 'UTF-8');
      }
      unset($this->postVars[$key]);
      $this->postVars[$key] = $cleanValue;
    }
  }

  /**
   * Método responsável por garantir que os parâmetros tenham conteúdo
   */
  private function validateParamsPost($postValidated)
  {
    foreach ($postValidated as $key) {
      if ($this->postVars[$key]) {
        $array[$key] = $this->postVars[$key];
      }
    }
    $this->postVars = $array;
  }
}
