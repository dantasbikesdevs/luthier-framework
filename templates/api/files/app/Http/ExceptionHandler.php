<?php

namespace App\Http;

use Luthier\Http\Response;
use Luthier\Http\IHandler;
use Throwable;

class ExceptionHandler implements IHandler
{
  /**
   * Erro gerado pela requisição.
   */
  private Throwable $error;

  /**
   * Mensagem do erro.
   */
  private mixed $message;

  /**
   * Código do erro.
   */
  private int $code;

  public function __construct(Throwable $error)
  {
    $this->error   = $error;
    $this->message = $error->getMessage();
    $this->code    = $error->getCode();
    $this->setMessageError();
  }

  /**
   * Retorna o código do erro.
   */
  private function setMessageError()
  {
    if ($this->code >= 500) {
      $this->message = "Não foi possível processar a requisição. Caso o erro persista, informe a equipe responsável.";
      return;
    }
  }

  /**
   * Retorna a mensagem do erro tratada para o usuário.
   */
  public function getResponse(): Response
  {
    return new Response(
      [
        "error" => $this->message,
      ],
      $this->code
    );
  }
}
