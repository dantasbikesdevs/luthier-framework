<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Log\Log;
use Exception;
use Luthier\Http\Response;
use Throwable;

class Handler
{
    /**
     * Erro lançado pelo sistema.
     */
    private Throwable $error;

    /**
     * Código de erro da exceção.
     */
    private int $code;

    /**
     * Mensagem de erro da exceção.
     */
    private string $message;

    public function __construct(Throwable $error)
    {
        $this->error = $error;
        $this->code = $error->getCode() < 200 ? 500 : $error->getCode();
        $this->message = $error->getMessage();
    }

    /**
     * Método responsável por registrar e tratar as exceções lançadas no sistema.
     */
    public function register(): void
    {
        if ($this->code == 500) {
            $uuid = $this->generateLog();
            $this->message = "Ocorreu um erro interno do servidor. Caso o problema persista, contate o suporte. ({$uuid})";
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

    /**
     * Método responsável por gerar o log da exceção e retornar o seu identificar único (uuid).
     */
    private function generateLog(): string
    {
        try {
            $logger = new Log("main");
            $logger->error("Erro ao processar requisição.", [
                "exception" => $this->error
            ]);

            return $logger->getUid();
        } catch (Throwable $throwable) {
            return "ERRLOG";
        }
    }
}