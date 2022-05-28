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

  /**
   * Usuário do banco
   */
  private string $user;

  /**
   * Senha de acesso ao banco de dados
   */
  private string $password;

  public function __construct(array $config)
  {
    $this->driver = $config["driver"];
    $this->path = $config["path"];
    $this->host = $config["host"];
    $this->user = $config["user"];
    $this->password = $config["password"];
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

  public function getUser()
  {
    return $this->user;
  }

  public function getPassword()
  {
    return $this->password;
  }
}
