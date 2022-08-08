<?php declare(strict_types=1);

namespace Luthier\Database;

class Pagination
{

  /**
   * Número máximo de registros por página
   */
  private int $limit;

  /**
   * Número do primeiro registro da página atual.
   */
  private int $first;

  /**
   * Quantidade total de resultados do banco
   */
  private int $results;

  /**
   * Quantidade de páginas
   */
  private int $pages;

  /**
   * Página atual
   */
  private int $currentPage;

  /**
   * Construtor da classe
   */
  public function __construct(int $results, int $currentPage = 1, int $limit = 10)
  {
    $this->results = $results;
    $this->limit = $limit;
    $this->currentPage = (is_numeric($currentPage) and $currentPage > 0) ? $currentPage : 1;
    $this->calculate();
  }

  /**
   * Método responsável por calcular a paginação
   */
  private function calculate()
  {
    // CALCULA O TOTAL DE PÁGINAS
    $this->pages = $this->results > 0 ? (int)ceil($this->results / $this->limit) : 1;

    // CALCULA O PRIMEIRO REGISTRO DA PÁGINA
    $this->first = ($this->limit * ($this->currentPage - 1));
  }

  /**
   * Método responsável por retornar a cláusula limit da SQL
   */
  public function getLimit()
  {
    return $this->limit;
  }

  /**
   * Método responsável por retornar as opções de páginas disponíveis
   */
  public function getPages(): array
  {
    // NÃO RETORNA PÁGINAS
    if ($this->pages == 1) return [];

    // PÁGINAS
    $pages = [];
    for ($i = 1; $i <= $this->pages; $i++) {
      $pages[] = [
        'page' => $i,
        'current' => $i == $this->currentPage
      ];
    }

    return $pages;
  }

  /**
   * Método responsável por o retornar nímero do primeiro registro da página
   */
  public function getFirst()
  {
    return $this->first;
  }

  /**
   * Método responsável por retornar a página atual.
   */
  public function getCurrentPage(): int
  {
    return $this->currentPage;
  }

  /**
   * Método responsável por retornar a quantidade de páginas.
   */
  public function getTotalPages(): int
  {
    return $this->pages;
  }

  /**
   * Método responsável por retornar a quantidade de resultados.
   */
  public function getCountResults(): int
  {
    return $this->results;
  }
}
