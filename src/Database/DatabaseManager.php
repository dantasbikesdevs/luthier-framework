<?php

namespace Luthier\Database;

class DatabaseManager
{
  /**
   * Driver do banco de dados
   */
  private string $driver;

  /**
   * Host de conexão com o banco de dados
   */
  private string $host;

  /**
   * Caminho para o banco de dados
   */
  private string $path;

  public function __construct(string $path, string $host, string $driver = "firebird")
  {
    $this->driver = $driver;
    $this->path = $path;
    $this->host = $host;
  }

  public function getDriver()
  {
    return $this->driver;
  }

  public function getPath()
  {
    return $this->path;
  }

  public function getHost()
  {
    return $this->host;
  }
}
