<?php

namespace App\Database;

/**
 * Classe de configuração de banco de dados
 */
class ConfigDatabase
{
  // ! API PÚBLICA
  /**
   * Retorna se a aplicação está em produção ou não!
   */
  public static function isInProduction(): bool
  {
    $development = getenv('ENV_PRODUCTION');
    return !$development;
  }

  /**
   * Retorna string de conexão com o banco de dados dependendo do ambiente
   */
  public static function getConnectionString(): string
  {
    $databaseManagementSystem = getenv('DATABASE_SYSTEM');

    $host = getenv('DEV_DB_HOST');
    $path = getenv('DEV_DB_PATH');
    $name = getenv('DEV_DB_NAME');

    if (self::isInProduction()) {
      $host = getenv('DB_HOST');
      $path = getenv('DB_PATH');
      $name = getenv('DB_NAME');
    }

    return self::connectionAsString($host, $path, $name, $databaseManagementSystem);
  }

  /**
   * Retorna usuário e senha do banco, verificando se está no ambiente de produção ou de desenvolvimento
   */
  public static function databaseUser()
  {
    if (self::isInProduction()) {
      return [
        'user' => getenv('DB_USER'),
        'pass' => getenv('DB_PASSWORD'),
      ];
    }

    return [
      'user' => 'SYSDBA',
      'pass' => 'masterkey'
    ];
  }

  // ! MÉTODOS INTERNOS
  /**
   * Gera a string de conexão
   */
  private static function connectionAsString($host, $path, $name, $databaseManagementSystem = "firebird")
  {
    return "$databaseManagementSystem:dbname=${host}:${path}${name};charset=utf8;dialect=3;";
  }
}
