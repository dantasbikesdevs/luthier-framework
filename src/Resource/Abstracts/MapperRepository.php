<?php

declare(strict_types=1);

namespace Luthier\Resource\Abstracts;

use Luthier\Database\Query;


abstract class MapperRepository extends Repository
{
    public function __construct(
        string $tableName,
        string $primaryKey,
        ?Query $queryBuilder = null
    ) {
        parent::__construct($tableName, $primaryKey, $queryBuilder);
    }

    /**
     * Método responsável por retornar um registro pelo seu ID.
     */
    public function findOne(int $id): ?object
    {
        $item = $this->queryBuilder->select()
            ->from($this->tableName)
            ->where("{$this->primaryKey} = :id")
            ->setParam("id", $id)
            ->first();

        if (!$item) return null;

        [$mappedItem] = $this->mappedObject([$item]);

        return $mappedItem;
    }

    /**
     * Método responsável por retornar um registro através de filtros.
     */
    public function findOneBy(array $filters): ?object
    {
        $item = $this->queryBuilder->select()
            ->from($this->tableName)
            ->filterWhere($filters)
            ->first();

        if (!$item) return null;

        [$mappedItem] = $this->mappedObject([$item]);

        return $mappedItem;
    }

    /**
     * Método responsável por retornar todos os registros da tabela a partir
     * dos filtros e com paginação, caso seja passada.
     */
    public function findAllBy(array $filters, string $firstSkip = "", string $orderBy = ""): array
    {
        $items = $this->queryBuilder->select("${firstSkip} *")
            ->from($this->tableName)
            ->filterWhere($filters)
            ->orderBy($orderBy)
            ->all();

        return $this->mappedObject($items);
    }

    /**
     * Método responsável por retornar todos os registros da tabela com paginação,
     * caso seja passada.
     */
    public function findAll(string $firstSkip = "", string $orderBy = ""): array
    {
        $items = $this->queryBuilder->select("${firstSkip} *")
            ->from($this->tableName)
            ->orderBy($orderBy)
            ->all();

        return $this->mappedObject($items);
    }

    /**
     * Método responsável por retornar um array de registro(s) e retorná-lo(s)
     * como objetos.
     */
    abstract protected function mappedObject(array $items): array;
}
