<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Luthier\Http\Response;

class Handler
{
    /**
     * Exceção lançanda pelo sistema.
     */
    private Exception $exception;

    /**
     * Código de erro da exceção.
     */
    private int $code;

    /**
     * Mensagem de erro da exceção.
     */
    private string $message;

    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
        $this->code = $exception->getCode() < 200 || $exception->getCode() >= 600 ? 600 : (int) $exception->getCode();
        $this->message = $exception->getMessage();
    }

    /**
     * Método responsável por registrar e tratar as exceções lançadas no sistema.
     */
    public function register(): void
    {
        if ($this->exception->getCode() >= 600) {
            $this->message = "Ocorreu um erro interno do servidor. Caso o problema persista, contate o suporte.";
        }

        $this->sendResponse();
    }

    /**
     * Método responsável por enviar a resposta para o cliente.
     */
    private function sendResponse(): void
    {
        $response = new Response([
            "error" => $this->message
        ], $this->code);

        $response->sendResponses();
    }
}