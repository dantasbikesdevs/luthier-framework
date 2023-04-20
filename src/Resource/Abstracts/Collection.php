<?php

declare(strict_types=1);

namespace Luthier\Resource\Abstracts;

abstract class Collection
{
    /**
     * Coleção de dados.
     */
    protected array $collection;

    public function __construct(array $collection = [])
    {
        $this->collection = $collection;
    }

    /**
     * Método responsável por adicionar vários itens
     * na coleção.
     */
    public function add(array $params): void
    {
        foreach ($params as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Método responsável por definir um item na coleção.
     */
    public function set(int|string $key, mixed $value): void
    {
        $this->collection[$key] = $value;
    }

    /**
     * Método responsável por retornar o primeiro item
     * da coleção.
     */
    public function first(): mixed
    {
        $key = array_key_first($this->collection);

        return $this->collection[$key];
    }

    /**
     * Método responsável por retornar o último item
     * da coleção.
     */
    public function last(): mixed
    {
        $key = array_key_last($this->collection);

        return $this->collection[$key];
    }

    /**
     * Método responsável por remover e retornar o primeiro
     * item da coleção.
     */
    public function shift(): mixed
    {
        return array_shift($this->collection);
    }

    /**
     * Método responsável por adicionar um item no início
     * da coleção.
     */
    public function unshift(mixed $value): void
    {
        array_unshift($this->collection, $value);
    }

    /**
     * Método responsável por adicionar um item no final
     * da coleção.
     */
    public function push(mixed $value): void
    {
        $this->collection[] = $value;
    }

    /**
     * Método responsável por remover o primeiro item
     * da coleção.
     */
    public function pop(): void
    {
        array_pop($this->collection);
    }

    /**
     * Método responsável por sobrescrever os itens a coleção.
     */
    public function replace(array $params = []): void
    {
        $this->collection = $params;
    }

    /**
     * Método responsável por informar se um item existe
     * na coleção.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->collection);
    }

    /**
     * Método responsável por remover um item da coleção.
     */
    public function remove(string $key): void
    {
        unset($this->collection[$key]);
    }

    /**
     * Método responsável por retornar o valor de um item
     * da coleção.
     */
    public function get(string $key): mixed
    {
        if ($this->has($key)) {
            return $this->collection[$key];
        }

        return null;
    }

    /**
     * Método responsável por retornar as chaves da coleção.
     */
    public function keys(): array
    {
        return array_keys($this->collection);
    }

    /**
     * Método responsável por retornar todos os itens
     * da coleção.
     */
    public function all(): array
    {
        return $this->collection;
    }
}
