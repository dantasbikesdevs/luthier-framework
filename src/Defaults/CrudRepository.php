<?php

namespace Luthier\Defaults;

abstract class CrudRepository
{
  public function create(mixed $object): mixed
  {
    return null;
  }

  public function createMany(mixed $objects): mixed
  {
    return null;
  }

  public function find(mixed $parameter, string $by = "id"): mixed
  {
    return null;
  }

  public function findMany(array $parameter, string $by = "id"): mixed
  {
    return null;
  }

  public function update(mixed $parameter, mixed $args, string $by = "id"): mixed
  {
    return null;
  }

  public function updateMany(array $parameter, array $args, string $by = "id"): mixed
  {
    return null;
  }


  public function delete(mixed $parameter, string $by = "id"): mixed
  {
    return null;
  }

  public function deleteMany(array $parameter, string $by = "id"): mixed
  {
    return null;
  }
}
