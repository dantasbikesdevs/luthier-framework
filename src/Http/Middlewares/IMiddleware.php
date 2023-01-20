<?php declare(strict_types=1);

namespace Luthier\Http\Middlewares;

use Closure;
use Luthier\Http\Request;
use Luthier\Http\Response;

interface IMiddleware
{
  public function handle(Request $request, Response $response, Closure $next): mixed;
}
