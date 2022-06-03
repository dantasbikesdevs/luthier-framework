<?php

namespace Luthier\Log;

use PHPMailer\PHPMailer\PHPMailer;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;

class PHPMailerHandler extends AbstractProcessingHandler
{
  /**
   * Instância do PHP Mailer
   */
  private PHPMailer $phpMailer;

  public function __construct(PHPMailer $phpMailer, int|string|Level $level = Level::Debug, bool $bubble = true)
  {
    $this->phpMailer = $phpMailer;
    parent::__construct($level, $bubble);
  }

  /**
   * Método da classe abstrata que executa o Handler.
   */
  protected function write($record): void
  {
    $this->phpMailer->isSMTP();
    $this->phpMailer->SMTPAuth = true;
    $this->phpMailer->SMTPSecure = "tls";
    $this->phpMailer->Body = $record["formatted"];
    $this->phpMailer->isHTML(true);
    $this->phpMailer->send();
  }
}
