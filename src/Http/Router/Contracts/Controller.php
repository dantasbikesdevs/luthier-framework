<?php

declare(strict_types=1);

namespace Luthier\Http\Router\Contracts;

interface Controller
{
    public function getClosure();
}
