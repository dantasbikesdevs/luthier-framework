<?php

namespace Luthier\Http;

use Exception;
use Luthier\Xml\XmlParser;

class Response
{

  /**
   * Código do Status HTTP
   */
  private int $httpCode = 200;

  /**
   * Cabeçalhos da requisição
   */
  private array $headers = [];

  /**
   * Tipo de conteúdo que está sendo retornado
   */
  private string $contentType = 'application/json';

  /**
   * Conteúdo do Response
   */
  private mixed $content;

  /**
   * Router da página
   */
  private Router $router;

  /**
   *  Construtor define os valores
   */
  public function __construct(Router $router)
  {
    // Objeto da classe router que é injetado na requisição
    $this->router = $router;
  }

  /**
   * Adiciona coisas ao corpo da resposta. Aqui vai o conteúdo que deseja enviar ao cliente.
   */
  public function body(mixed $content)
  {
    $this->content = $content;
    return $this;
  }

  /**
   * Método responsável por retornar o router
   */
  public function getRouter(): Router
  {
    return $this->router;
  }

  /**
   * Método responsável por alterar o contentType do response
   */
  public function setContentType($contentType)
  {
    $this->contentType = $contentType;
    $this->addHeader('Content-Type', $contentType);
  }

  /**
   * Método responsável por adicionar um registro nos Headers do response
   */
  public function addHeader(string $key, string $value)
  {
    $this->headers[$key] = $value;
  }

  /**
   * Método responsável por enviar a resposta ao usuário. Chamado ao utilizarmos o método run() do router.
   */
  public function sendResponses()
  {
    // Envia os cabeçalhos setados anteriormente
    $this->sendHeaders();

    // Entrega o conteúdo
    echo match ($this->contentType) {
      'application/json' => json_encode($this->content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      'text/xml' => XmlParser::xmlEncode(['content' => array('Status' => $this->httpCode, 'Dados' => $this->content)], 'UTF-8'),
      default => $this->content
    };
  }

  /**
   * Função responsável pelas requisições de download
   */
  public function download($size, $filename): self
  {
    return $this;
  }

  //* Tipos de conteúdo da resposta. Deve ser a penúltima coisa na composição da resposta.

  /**
   * Função responsável por configurar requisição para PDF
   */
  public function asPdf(): self
  {
    $type = 'application/pdf';
    $this->getRouter()->setContentType($type);
    $this->contentType = $type;
    return $this;
  }

  /**
   * Função responsável por configurar requisição para JSON
   */
  public function asJson(): self
  {
    $type = 'application/json';
    $this->getRouter()->setContentType($type);
    $this->contentType = $type;
    return $this;
  }

  /**
   * Função responsável por configurar requisição para XML
   */
  public function asXml(): self
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

    // Caso haja erro
    if ($code >= 400) {
      throw new Exception($content, $code);
    }

    // Compõe o objeto de resposta
    $response = new Response($this->router);
    $response->setCode($code);
    $response->body($content);
    $response->setContentType($this->contentType);

    return $response;
    return new Response($this->router, $code, $content, $this->contentType);
  }

  // * Códigos de resposta HTTP. Última coisa a ser colocada na resposta.

  /**
   * Função responsável por indicar sucesso na requisição
   */
  public function ok()
  {
    return $this->httpResponse(200, $this->content);
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
   * Setter para o código da resposta
   */
  public function setCode(int $code)
  {
    $this->httpCode = $code;
  }

  // ! MÉTODOS INTERNOS

  /**
   * Método responsável por enviar os headers ao navegador
   */
  private function sendHeaders()
  {
    // Status
    http_response_code($this->httpCode);
    // Cria cada headers
    foreach ($this->headers as $key => $value) {
      header($key . ':' . $value);
    }
  }
}
