<?php declare(strict_types=1);

namespace Luthier\Log;

use App\Models\Entity\UserEntity;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use PHPMailer\PHPMailer\PHPMailer;
use \Psr\Log\InvalidArgumentException;
use Luthier\Log\PHPMailerHandler;
use Luthier\Utils\Validate;
use Luthier\Http\Request;
use Luthier\Log\LogManager;

class Log extends Logger
{

  /**
   * Canal do Logger
   */
  private string $channel;

  /**
   * Canais de Loggers definidos pelo usuário.
   */
  private static array $channels = [];

  /**
   * Formato para gravação do tempo.
   */
  private string $timeFormat = "Y-m-d H:i:s";

  /**
   * Usuário autenticado que gerou o erro.
   */
  private ?UserEntity $user;

  /**
   * Método responsável por setar os canais definidos pelo usuário.
   */
  public static function config(LogManager $manager)
  {
    self::$channels = $manager->getChannels();
  }

  public function __construct(string $channel = null, bool $default = true)
  {
    $this->channel  = $channel ?? "main";
    $this->user     = Request::getUser();
    parent::__construct($this->channel);
    if ($default) $this->loadSettings();
  }

  /**
   * Configura alguns canais pré-definidos a partir do arquivo de configuração de log.
   */
  private function loadSettings(): void
  {
    if (empty(self::$channels))
      throw new InvalidArgumentException("Nenhum canal de log foi definido.");

    $channel = self::$channels[$this->channel];

    if (!isset($channel))
      throw new InvalidArgumentException("Channel {$this->channel} not found in config/logging.php");

    // Verifica se foi definido logs em arquivo para o canal informado e o inicia
    if (isset($channel["file"])) {
      $this->setFile($channel["file"]);
    }

    // Verifica se foi definido logs em e-mail para o canal informado e o inicia
    if (isset($channel["email"])) {
      $this->setEmail($channel["email"]);
    }

    // Verifica se foi definido logs no telegram para o canal informado e o inicia
    if (isset($channel["telegram"])) {
      $this->setTelegram($channel["telegram"]);
    }

    // Seta processadores padrões de informações que irão sair no log
    $this->pushProcessor(function ($record) {
      $record["extra"]["REMOTE_ADDR"]        = $_SERVER["REMOTE_ADDR"] ?? "Not identified";
      $record["extra"]["SERVER_PROTOCOL"]    = $_SERVER["SERVER_PROTOCOL"] ?? "Not identified";
      $record["extra"]["REQUEST_URI"]        = $_SERVER["REQUEST_URI"] ?? "Not identified";
      $record["extra"]["REQUEST_METHOD"]     = $_SERVER["REQUEST_METHOD"] ?? "Not identified";
      $record["extra"]["HTTP_USER_AGENT"]    = $_SERVER["HTTP_USER_AGENT"] ?? "Not identified";
      $record["extra"]["CONTENT_TYPE"]       = $_SERVER["CONTENT_TYPE"] ?? "Not identified";
      $record["extra"]["USER_ID"]            = isset($this->user) ? $this->user->getId() : "No authenticated";
      return $record;
    });
    $this->pushProcessor(new \Monolog\Processor\PsrLogMessageProcessor());
    $this->pushProcessor(new \Monolog\Processor\UidProcessor());
    $this->pushProcessor(new \Monolog\Processor\IntrospectionProcessor());
  }

  /**
   * Seta o arquivo de log.
   * @param array $variables[path, level]
   */
  private function setFile(array $variables): void
  {
    Validate::paramsRequired([
      "path"  => $variables["path"] ?? null,
      "level" => $variables["level"] ?? null,
    ], 500);

    $fileHandler = new StreamHandler(
      $variables["path"],
      $variables["level"]
    );
    $fileHandler->setFormatter(
      new LineFormatter(
        "[%datetime%] %channel%.%level_name%: %message% %context% %extra%" . PHP_EOL,
        $this->timeFormat
      )
    );
    $this->pushHandler($fileHandler);
  }

  /**
   * Seta o email de log.
   * @param array $variables[host, port, username, password, from, to, subject, level]
   */
  private function setEmail(array $variables): void
  {
    Validate::paramsRequired([
      "host"     => $variables["host"] ?? null,
      "port"     => $variables["port"] ?? null,
      "username" => $variables["username"] ?? null,
      "password" => $variables["password"] ?? null,
      "from"     => $variables["from"] ?? null,
      "to"       => $variables["to"] ?? null,
      "subject"  => $variables["subject"] ?? null,
      "level"    => $variables["level"] ?? null,
    ], 500);

    $phpMailer           = new PHPMailer();
    $phpMailer->Host     = $variables["host"];
    $phpMailer->Port     = $variables["port"];
    $phpMailer->Username = $variables["username"];
    $phpMailer->Password = $variables["password"];
    $phpMailer->setFrom($variables["from"]);
    $phpMailer->addAddress($variables["to"]);
    $phpMailer->Subject  = $variables["subject"];

    $emailHandler = new PHPMailerHandler($phpMailer, $variables["level"]);
    $emailHandler->setFormatter(
      new HtmlFormatter($this->timeFormat)
    );

    $this->pushHandler($emailHandler);
  }

  /**
   * Seta o bot do Telegram de log.
   * @param array $variables[apiKey, channel, level]
   */
  private function setTelegram(array $variables): void
  {
    Validate::paramsRequired([
      "apiKey"  => $variables["apiKey"] ?? null,
      "channel" => $variables["channel"] ?? null,
      "level"   => $variables["level"] ?? null
    ], 500);

    $telegramHandler = new TelegramBotHandler(
      $variables["apiKey"],
      $variables["channel"],
      $variables["level"]
    );
    $telegramHandler->setFormatter(
      new LineFormatter(
        "%level_name%: %message%",
        $this->timeFormat
      )
    );
    $this->pushHandler($telegramHandler);
  }

  /**
   * Seta o formato do tempo.
   * @param string $format
   */
  public function setTimeFormat(string $timeFormat): void
  {
    $this->timeFormat = $timeFormat;
  }
}
