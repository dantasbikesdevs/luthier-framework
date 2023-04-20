<?php

declare(strict_types=1);

namespace Luthier\Http\Router\Collections;

use Luthier\Resource\Abstracts\Collection;

class MiddlewareCollection extends Collection
{
    public function __construct(array $collection = [])
    {
        parent::__construct($collection);
    }
}
