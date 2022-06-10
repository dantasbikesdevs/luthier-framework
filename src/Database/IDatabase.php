<?php declare(strict_types=1);

namespace Luthier\Database;

use PDO;

interface IDatabase
{
  /**
   * Método responsável por configurar a classe
   */
  public function __construct(DatabaseManager $databaseManager);

  /**
   * Retorna a conexão realizada com a tabela pelo PDO
   */
  public function getConnection();
}
