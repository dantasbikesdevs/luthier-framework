<?php

namespace Luthier\Http;

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
   *  Construtor define os valores
   */
  public function __construct(int $httpCode, mixed $content, string $contentType = 'application/json')
  {
    $this->httpCode = $httpCode;
    $this->content = $content;
    $this->setContentType($contentType);
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
   * Método responsável por enviar a resposta ao usuário
   */
  public function sendResponse()
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
