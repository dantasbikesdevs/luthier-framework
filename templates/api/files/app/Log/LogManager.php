<?php declare(strict_types=1);

namespace App\Log;

class LogManager
{
  /**
   * Canais de Loggers definidos pelo usuário.
   */
  private array $channels = [];

  public function __construct(array $channels)
  {
    $this->channels = $channels;
  }

  /**
   * Método responsável por retornar os canais definidos pelo usuário.
   */
  public function getChannels(): array
  {
    return $this->channels;
  }
}
