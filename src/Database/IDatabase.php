<?php

interface IDatabase
{
  /**
   * Método responsável por configurar a classe
   */
  public static function config(string $driver, string $host, string $path, string $user, string $pass);

  /**
   * Define a tabela e instancia a conexão
   */
  public function __construct(?string $tableName = null);

  /**
   * Retorna a conexão realizada com a tabela pelo PDO
   */
  public function getConnection(): PDO;

  /**
   * Retorna o nome da tabela
   */
  public function getTableName(): string;
}
