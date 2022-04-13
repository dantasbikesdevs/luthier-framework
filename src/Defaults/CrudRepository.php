<?php

namespace Luthier\Defaults;

abstract class CrudRepository
{
  abstract public function create(mixed $object): mixed;

  abstract public function createMany(array $objects): mixed;

  abstract public function find(mixed $parameter, string $by = "id"): mixed;

  abstract public function findMany(array $parameter = [], string $by = "id"): mixed;

  abstract public function update(mixed $parameter, mixed $args, string $by = "id"): mixed;

  abstract public function updateMany(array $parameter, array $args, string $by = "id"): mixed;

  abstract public function delete(mixed $parameter, string $by = "id"): mixed;

  abstract public function deleteMany(array $parameter, string $by = "id"): mixed;
}
