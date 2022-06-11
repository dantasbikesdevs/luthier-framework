<?php declare(strict_types=1);

namespace Luthier\Database;

use App\Database\ApplicationDatabase;
use Closure;
use PDO;

class Transaction
{
  protected bool $hasActiveTransaction = false;
  private PDO $connection;

  public function __construct(PDO $connection = null)
  {
    $this->connection = $connection ?? ApplicationDatabase::getConnection();
  }

  /**
   * Inicia uma transação com o bando de dados (Precisa de um commit / rollback)
   */
  public function begin()
  {
    if ($this->hasActiveTransaction) {
      return false;
    }

    $this->connection->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
    $this->hasActiveTransaction = true;
    return $this->connection->beginTransaction();
  }

  /**
   * Dá rollback em uma transação com o bando de dados (Precisa ser chamado depois de begin)
   */
  public function rollback()
  {
    if ($this->hasActiveTransaction) {
      $this->hasActiveTransaction = false;
      return $this->connection->rollBack();
    }

    return false;
  }

  /**
   * Dá commit em uma transação com o bando de dados (Precisa ser chamado depois de begin)
   */
  public function commit()
  {
    if ($this->hasActiveTransaction) {
      $this->hasActiveTransaction = false;
      return $this->connection->commit();
    }

    return false;
  }

  /**
   * Cria uma transação que dá rollback automaticamente quando um erro é lançado.
   * O commit ao banco de dados é feito automaticamente caso as operações sejam bem sucedidas.
   */
  public function panicRollback(Closure $databaseAction)
  {
    $this->begin();
    try {
      // Tenta executar uma ação no banco de dados
      $result = $databaseAction();
      $this->commit();
      return $result;
    } catch (\Throwable $th) {
      // Dá rollback antes de lançar o erro
      $this->rollback();
      throw $th;
    }
    return $result;
  }
}
