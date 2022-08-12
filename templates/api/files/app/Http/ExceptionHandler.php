<?php

declare(strict_types=1);

namespace App\Http;

use App\Log\Log;
use Luthier\Http\Response;
use Throwable;

class ExceptionHandler
{
    /**
     * Erro gerado pela requisição.
     */
    private static Throwable $error;

    /**
     * Mensagem de erro da exceção.
     */
    private static string $message;

    /**
     * Código de erro da exceção.
     */
    private static int $code;

    /**
     * Inicializa a classe com a exceção gerada.
     */
    public static function init(Throwable $error): void
    {
        self::$error = $error;
        self::$message = $error->getMessage();
        self::$code = $error->getCode() < 200 || $error->getCode() >= 600 ? 500 : $error->getCode();
        self::setError();
        self::sendResponse();
    }

    /**
     * Retorna a resposta para o usuário.
     */
    private static function sendResponse(): void
    {
        $response = new Response([
            "erro" => self::$message
        ], self::$code);

        $response->sendResponses();

        if (self::$code >= 500) throw self::$error;
    }

    /**
     * Método que seta a mensagem tratada a depender do código e/ou tipo da exceção.
     */
    private static function setError(): void
    {
        try {
            if (self::$code >= 500) {
                self::error500();
            }
        } catch (Throwable $error) {
            self::$error = $error;
            self::$message = "Ocorreu um erro inesperado. Caso o erro persista, contate o suporte.";
        }
    }

    /**
     * Método que realiza as operações quando o erro retornado for 500.
     */
    private static function error500(): void
    {
        self::$message = "Não foi possível processar a requisição. Caso o erro persista, contate o suporte.";
    }
}