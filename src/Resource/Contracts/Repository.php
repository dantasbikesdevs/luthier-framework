<?php

declare(strict_types=1);

namespace Luthier\Resource\Contracts;

interface Repository
{
  public function findOne(int $id);
  public function findOneBy(array $filters);
  public function findAll(string $firstSkip = "", string $orderBy = ""): array;
  public function findAllBy(array $filters, string $firstSkip = "", string $orderBy = ""): array;
  public function count(array $filters = []): int;
}
