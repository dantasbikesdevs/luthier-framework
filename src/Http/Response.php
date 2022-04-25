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
  public function send(mixed $content)
  {
    $this->content = $content;
    return $this->httpResponse($this->httpCode, $this->content);
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
    $response->setContent($content);
    $response->setContentType($this->contentType);

    return $response;
    return new Response($this->router, $code, $content, $this->contentType);
  }

  // * Códigos de resposta HTTP. Última coisa a ser colocada na resposta.

  /**
   * Função responsável por indicar sucesso na requisição
   */
  public function ok(): Response
  {
    $this->setCode(200);
    return $this;
  }

  /**
   * Função responsável por indicar sucesso na criação de conteúdo
   */
  public function created(): Response
  {
    $this->setCode(201);
    return $this;
  }

  /**
   * Função responsável por indicar que um recurso foi movido permanentemente
   */
  public function movedPermanently(): Response
  {
    $this->setCode(301);
    return $this;
  }

  /**
   * Função responsável por indicar que você esta sendo redirecionado
   */
  public function seeOther(): Response
  {
    $this->setCode(303);
    return $this;
  }

  /**
   * Recurso movido permanentemente
   */
  public function permanentRedirect(): Response
  {
    $this->setCode(308);
    return $this;
  }


  /**
   * Requisição mal formada
   */
  public function badRequest(): Response
  {
    $this->setCode(400);
    return $this;
  }

  /**
   * Requisição não autorizada
   */
  public function unauthorized(): Response
  {
    $this->setCode(401);
    return $this;
  }

  /**
   * O servidor não autorizou a emissão de um resposta.
   */
  public function paymentRequired(): Response
  {
    $this->setCode(402);
    return $this;
  }

  /**
   * O servidor recebeu a requisição e foi capaz de identificar o autor, porém não autorizou a emissão de um resposta.
   */
  public function forbidden(): Response
  {
    $this->setCode(403);
    return $this;
  }

  /**
   * Função responsável por indicar que um conteúdo não foi encontrado
   */
  public function notFound(): Response
  {
    $this->setCode(404);
    return $this;
  }

  /**
   * O servidor recebeu a requisição e se nega a enviar uma resposta por conta do protocolo não ser suportado ou
   * por conta de um user-agent ruim, por exemplo.
   */
  public function notAcceptable(): Response
  {
    $this->setCode(405);
    return $this;
  }

  /**
   * O servidor recebeu a requisição e se nega a enviar uma resposta por conta do protocolo não ser suportado ou
   * por conta de um user-agent ruim, por exemplo.
   */
  protected function setContent(mixed $content): Response
  {
    $this->content = $content;
    return $this;
  }

  /**
   * O servidor recebeu a requisição e demorou demais para processá-la
   */
  public function requestTimeout(): Response
  {
    $this->setCode(408);
    return $this;
  }

  /**
   * Função responsável por indicar conflito na requisição
   */
  public function conflict(): Response
  {
    $this->setCode(409);
    return $this;
  }

  /**
   * Função responsável por indicar conflito na requisição
   */
  public function unsupportedMediaType(): Response
  {
    $this->setCode(415);
    return $this;
  }

  /**
   * Função responsável por indicar erro no servidor
   */
  public function internalServerError(): Response
  {
    $this->setCode(500);
    return $this;
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
