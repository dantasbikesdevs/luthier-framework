<?php declare(strict_types=1);

namespace Luthier\Http;

use Luthier\Exceptions\ResponseException;
use Luthier\Utils\Transform;
use Luthier\Xml\XmlParser;

class Response
{

  /**
   * Código do Status HTTP da resposta
   */
  private int $httpCode = 200;

  /**
   * Cabeçalhos da resposta
   */
  private array $headers = [];

  /**
   * Tipo de conteúdo que está sendo retornado
   */
  private string $contentType = "application/json";

  /**
   * Conteúdo da resposta
   */
  private mixed $content;

  /**
   * Charset da resposta
   */
  private string $charset = "utf-8";

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
  public function send(mixed $content, int $code = 200): Response
  {
    $this->setCode($code);
    $this->setContent($content);
    return $this->httpResponse();
  }

  /**
   * O servidor recebeu a requisição e se nega a enviar uma resposta por conta do protocolo não ser suportado ou
   * por conta de um user-agent ruim, por exemplo.
   */
  public function setContent(mixed $content): Response
  {
    $content = $this->sanitize($content);
    $this->content = $content;
    return $this;
  }

  /**
   * Getter para  o conteúdo da resposta
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
  public function getCode(): int
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
  public function getContentType(): string
  {
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
  public function getHeader(string $header)
  {
    return $this->headers[$header] ?? null;
  }

  /**
   * Método responsável por alterar o charset da resposta
   */
  public function setCharset(string $charset): Response
  {
    $this->charset = $charset;
    return $this;
  }

  /**
   * Getter para o charset da resposta
   */
  public function getCharset(): string
  {
    return $this->charset;
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
   * Método responsável pelas requisições de download
   */
  public function download($size, $filename): self
  {
    return $this;
  }

  //* Tipos de conteúdo da resposta. Deve ser a penúltima coisa na composição da resposta.

  /**
   * Método responsável por configurar requisição para PDF
   */
  public function asPdf(): self
  {
    $type = 'application/pdf';
    $this->contentType = $type;
    return $this;
  }

  /**
   * Método responsável por configurar requisição para JSON
   */
  public function asJson(): self
  {
    $type = 'application/json';
    $this->contentType = $type;
    return $this;
  }

  /**
   * Método responsável por configurar requisição para XML
   */
  public function asXml(): self
  {
    $type = 'text/xml';
    $this->contentType = $type;
    return $this;
  }

  /**
   * Método responsável por enviar uma resposta ou erro
   */
  private function httpResponse(): Response
  {
    $code = $this->httpCode;
    $content = $this->content;

    if (!is_array($content) && !is_object($content)) {
      if ($code >=400) {
        $content = ["erro" => $content];
      } else {
        $content = ["mensagem" => $content];
      }
    }

    $response = new Response();
    $response->setCode($code);
    $response->setContent($content);
    $response->setContentType($this->contentType);
    return $response;
  }

  // * Códigos de resposta HTTP. Última coisa a ser colocada na resposta.

  /**
   * Método responsável por indicar sucesso na requisição
   */
  public function ok(mixed $content): Response
  {
    return $this->send($content);
  }

  /**
   * Método responsável por indicar sucesso na criação de conteúdo
   */
  public function created(mixed $content): Response
  {
    return $this->send($content, 201);
  }

  /**
   * Método responsável por indicar que um recurso foi movido permanentemente
   */
  public function movedPermanently(mixed $content): Response
  {
    return $this->send($content, 301);
  }

  /**
   * Método responsável por indicar que você esta sendo redirecionado
   */
  public function seeOther(mixed $content): Response
  {
    return $this->send($content, 303);
  }

  /**
   * Método responsável por indicar que a resposta não foi modificada
   */
  public function notModified(mixed $content): Response
  {
    return $this->send($content, 304);
  }

  /**
   * Recurso movido permanentemente
   */
  public function permanentRedirect(mixed $content): Response
  {
    return $this->send($content, 308);
  }


  /**
   * Requisição mal formada
   */
  public function badRequest(mixed $content): Response
  {
    return $this->send($content, 400);
  }

  /**
   * Requisição não autorizada
   */
  public function unauthorized(mixed $content): Response
  {
    return $this->send($content, 401);
  }

  /**
   * O servidor não autorizou a emissão de um resposta.
   */
  public function paymentRequired(mixed $content): Response
  {
    return $this->send($content, 402);
  }

  /**
   * O servidor recebeu a requisição e foi capaz de identificar o autor, porém não autorizou a emissão de um resposta.
   */
  public function forbidden(mixed $content): Response
  {
    return $this->send($content, 403);
  }

  /**
   * Método responsável por indicar que um conteúdo não foi encontrado
   */
  public function notFound(mixed $content): Response
  {
    return $this->send($content, 404);
  }

  /**
   * O servidor recebeu a requisição e se nega a enviar uma resposta por conta do protocolo não ser suportado ou
   * por conta de um user-agent ruim, por exemplo.
   */
  public function notAcceptable(mixed $content): Response
  {
    return $this->send($content, 405);
  }

  /**
   * O servidor recebeu a requisição e demorou demais para processá-la
   */
  public function requestTimeout(mixed $content): Response
  {
    return $this->send($content, 408);
  }

  /**
   * Método responsável por indicar conflito na requisição
   */
  public function conflict(mixed $content): Response
  {
    return $this->send($content, 409);
  }

  /**
   * Método responsável por indicar conflito na requisição
   */
  public function unsupportedMediaType(mixed $content): Response
  {
    return $this->send($content, 415);
  }

  /**
   * Método responsável por indicar erro no servidor
   */
  public function internalServerError(mixed $content): Response
  {
    return $this->send($content, 500);
  }

  // ! MÉTODOS INTERNOS
  /**
   * Método responsável por sanitizar a resposta para o cliente
   */
  private function sanitize(mixed $content): mixed
  {
    if (is_object($content)) {
      $content = Transform::objectToArray($content);
    }

    if (!is_array($content)) {
      return $this->cleanValue($content);
    }

    foreach ($content as $key => $value) {
      $cleanValue = $value;
      if (is_array($cleanValue)) {
        $this->sanitize($cleanValue);
      } else if (isset($cleanValue)){
        $cleanValue = $this->cleanValue($cleanValue);
        $content[$key] = $cleanValue;
      }
    }

    return $content;
  }

  /**
   * Método responsável por "limpar" a string recebida
   */
  private function cleanValue(mixed $value): mixed
  {
    if(!is_string($value)) {
      return $value;
    };

    $cleanValue = strip_tags(trim($value));
    $cleanValue = htmlspecialchars($cleanValue);

    return $cleanValue;
  }

  /**
   * Método responsável por enviar os headers ao navegador
   */
  private function sendHeaders()
  {
    // Status
    http_response_code($this->httpCode);

    $contentType = $this->getHeader("Content-Type");
    if (!$contentType) $this->setHeaders(['Content-Type' => $this->getContentType()]);

    // Cria cada headers
    foreach ($this->headers as $key => $value) {
      header($key . ': ' . $value);
    }
  }
}
