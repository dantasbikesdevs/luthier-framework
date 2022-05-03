<?php

namespace Luthier\Database;

class DatabaseUser
{

  /**
   * Usuário do banco
   */
  private string $user;

  /**
   * Senha de acesso ao banco de dados
   */
  private string $password;

  public function __construct(string $user, string $password)
  {
    $this->user = $user;
    $this->password = $password;
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
