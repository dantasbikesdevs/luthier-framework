<?php

declare(strict_types=1);

namespace Luthier\Resource\Abstracts;

use Luthier\Database\Pagination;
use Luthier\Database\Query;
use Luthier\Exceptions\PaginationException;
use Luthier\Resource\Contracts\Repository as RepositoryInterface;

abstract class Repository implements RepositoryInterface
{
    /**
     * Nome da tabela do repositório.
     */
    protected string $tableName;

    /**
     * Chave primária da tabela do repositório.
     */
    protected string $primaryKey;

    /**
     * Instância de queryBuilder.
     */
    protected Query $queryBuilder;

    public function __construct(
        string $tableName,
        string $primaryKey,
        ?Query $queryBuilder = null
    ) {
        $this->tableName = $tableName;
        $this->primaryKey = $primaryKey;
        $this->queryBuilder = $queryBuilder ?? new Query();
    }

    /**
     * Método responsável por retornar um registro pelo seu ID.
     */
    public function findOne(int $id)
    {
        $item = $this->queryBuilder->select()
            ->from($this->tableName)
            ->where("{$this->primaryKey} = :id")
            ->setParam("id", $id)
            ->first();

        if (!$item) return null;

        return $item;
    }

    /**
     * Método responsável por retornar um registro através de filtros.
     */
    public function findOneBy(array $filters)
    {
        $item = $this->queryBuilder->select()
            ->from($this->tableName)
            ->filterWhere($filters)
            ->first();

        if (!$item) return null;

        return $item;
    }

    /**
     * Método responsável por retornar todos os registros da tabela a partir
     * dos filtros e com paginação, caso seja passada.
     */
    public function findAllBy(array $filters, string $firstSkip = "", string $orderBy = ""): array
    {
        return $this->queryBuilder->select("${firstSkip} *")
            ->from($this->tableName)
            ->filterWhere($filters)
            ->orderBy($orderBy)
            ->all();
    }

    /**
     * Método responsável por retornar todos os registros da tabela com paginação,
     * caso seja passada.
     */
    public function findAll(string $firstSkip = "", string $orderBy = ""): array
    {
        return $this->queryBuilder->select("${firstSkip} *")
            ->from($this->tableName)
            ->orderBy($orderBy)
            ->all();
    }

    /**
     * Método responsável por retornar a quantidade de registros da tabela
     * a partir dos filtros.
     */
    public function count(array $filters = []): int
    {
        $items = $this->queryBuilder->select("COUNT(*)")
            ->from($this->tableName)
            ->filterWhere($filters)
            ->first();

        return $items["COUNT"];
    }

    /**
     * Método responsável por gerar a paginação.
     */
    public function generatePagination(int $currentPage, array $filters = [], int $limit = 10): Pagination
    {
        $count = $this->count($filters);

        $pagination = new Pagination($count, $currentPage, $limit);

        if ($pagination->isExceededPages())
            throw new PaginationException("Página atual excede o total de páginas.");

        return $pagination;
    }

    /**
     * Método responsável por buscar os pedidos baseado na paginação e filtros.
     */
    public function getPaginated(Pagination $pagination, array $filters = [], string $orderBy = ""): array
    {
        $limit = $pagination->getLimit();
        $first = $pagination->getFirst();

        $firstSkip = "FIRST ${limit} SKIP ${first}";
        return $this->findAllBy($filters, $firstSkip);
    }
}
