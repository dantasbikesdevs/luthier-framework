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
  private mixed $content = "";

  /**
   *  Construtor define os valores
   */
  public function __construct(mixed $content = "", int $httpCode = 200, array $headers = [])
  {
    $this->setContent($content);
    $this->setCode($httpCode);
    $this->setHeaders($headers);
  }

  /**
   * Adiciona coisas ao corpo da resposta. Aqui vai o conteúdo que deseja enviar ao cliente.
   */
  public function send(mixed $content, int $code = 200)
  {
    $this->httpCode = $code;
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * O servidor recebeu a requisição e se nega a enviar uma resposta por conta do protocolo não ser suportado ou
   * por conta de um user-agent ruim, por exemplo.
   */
  public function setContent(mixed $content): Response
  {
    $this->content = $content;
    return $this;
  }

  /**
   * Getter para o conteúdo da resposta
   */
  public function getContent(): mixed
  {
    return $this->content;
  }

  /**
   * Setter para o código da resposta
   */
  public function setCode(int $code): Response
  {
    $this->httpCode = $code;
    return $this;
  }

  /**
   * Getter para o código da resposta
   */
  public function getCode(int $code): int
  {
    return $this->httpCode;
  }

  /**
   * Método responsável por alterar o contentType da resposta
   */
  public function setContentType($contentType): Response
  {
    $this->contentType = $contentType;
    return $this;
  }

  /**
   * Getter para o contentType da resposta
   */
  public function getContentType(): string {
    return $this->contentType;
  }

  /**
   * Método responsável por adicionar um registro nos Headers do response
   */
  public function setHeaders(array $headers): Response
  {
    foreach ($headers as $key => $value) {
      $this->headers[$key] = $value;
    }
    return $this;
  }

  /**
   * Getter para retornar um header
   */
  public function getHeader(string $header) {
    return $this->headers[$header] ?? null;
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
    $this->contentType = $type;
    return $this;
  }

  /**
   * Função responsável por configurar requisição para JSON
   */
  public function asJson(): self
  {
    $type = 'application/json';
    $this->contentType = $type;
    return $this;
  }

  /**
   * Função responsável por configurar requisição para XML
   */
  public function asXml(): self
  {
    $type = 'text/xml';
    $this->contentType = $type;
    return $this;
  }

  /**
   * Função responsável por enviar uma resposta ou erro
   */
  public function httpResponse(): Response
  {
    $code = $this->httpCode;
    $content = $this->content;

    if(empty($content)) {
      $code = 500;
      $content = ["mensagem" => "Não foi possível encontrar o conteúdo da requisição."];
    }

    // Caso haja erro
    if ($code >= 500 || $code == 0) {
      throw new Exception($content, 500);
    }

    // Compõe o objeto de resposta
    if(!is_array($content) && !is_object($content)) {
      $content = ["mensagem" => $content];
    }

    $response = new Response();
    $response->setCode($code);
    $response->setContent($content);
    $response->setContentType($this->contentType);
    return $response;
  }

  // * Códigos de resposta HTTP. Última coisa a ser colocada na resposta.

  /**
   * Função responsável por indicar sucesso na requisição
   */
  public function ok(mixed $content): Response
  {
    $this->setCode(200);
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * Função responsável por indicar sucesso na criação de conteúdo
   */
  public function created(mixed $content): Response
  {
    $this->setCode(201);
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * Função responsável por indicar que um recurso foi movido permanentemente
   */
  public function movedPermanently(mixed $content): Response
  {
    $this->setCode(301);
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * Função responsável por indicar que você esta sendo redirecionado
   */
  public function seeOther(mixed $content): Response
  {
    $this->setCode(303);
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * Recurso movido permanentemente
   */
  public function permanentRedirect(mixed $content): Response
  {
    $this->setCode(308);
    $this->content = $content;
    return $this->httpResponse();
  }


  /**
   * Requisição mal formada
   */
  public function badRequest(mixed $content): Response
  {
    $this->setCode(400);
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * Requisição não autorizada
   */
  public function unauthorized(mixed $content): Response
  {
    $this->setCode(401);
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * O servidor não autorizou a emissão de um resposta.
   */
  public function paymentRequired(mixed $content): Response
  {
    $this->setCode(402);
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * O servidor recebeu a requisição e foi capaz de identificar o autor, porém não autorizou a emissão de um resposta.
   */
  public function forbidden(mixed $content): Response
  {
    $this->setCode(403);
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * Função responsável por indicar que um conteúdo não foi encontrado
   */
  public function notFound(mixed $content): Response
  {
    $this->setCode(404);
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * O servidor recebeu a requisição e se nega a enviar uma resposta por conta do protocolo não ser suportado ou
   * por conta de um user-agent ruim, por exemplo.
   */
  public function notAcceptable(mixed $content): Response
  {
    $this->setCode(405);
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * O servidor recebeu a requisição e demorou demais para processá-la
   */
  public function requestTimeout(mixed $content): Response
  {
    $this->setCode(408);
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * Função responsável por indicar conflito na requisição
   */
  public function conflict(mixed $content): Response
  {
    $this->setCode(409);
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * Função responsável por indicar conflito na requisição
   */
  public function unsupportedMediaType(mixed $content): Response
  {
    $this->setCode(415);
    $this->content = $content;
    return $this->httpResponse();
  }

  /**
   * Função responsável por indicar erro no servidor
   */
  public function internalServerError(mixed $content): Response
  {
    $this->setCode(500);
    $this->content = $content;
    return $this->httpResponse();
  }

  // ! MÉTODOS INTERNOS

  /**
   * Método responsável por enviar os headers ao navegador
   */
  private function sendHeaders()
  {
    // Status
    http_response_code($this->httpCode);

    $contentType = $this->getHeader("Content-Type");
    if(!$contentType) $this->setHeaders(['Content-Type' => $this->getContentType()]);

    // Cria cada headers
    foreach ($this->headers as $key => $value) {
      header($key . ': ' . $value);
    }
  }
}
