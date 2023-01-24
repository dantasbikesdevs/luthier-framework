<?php

namespace Luthier\Http\Router;

use Luthier\Http\Request;
use Luthier\Http\Router\Abstracts\Route as AbstractRoute;

class Route extends AbstractRoute
{
    public function __construct(
        string  $method,
        string  $uri,
        Request $request
    )
    {
        parent::__construct($method, $uri, $request);
    }
}
