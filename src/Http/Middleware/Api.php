<?php

namespace Luthier\Http\Middleware;

use Closure;
use Luthier\Http\Middleware\IMiddleware;
use Luthier\Http\Request;
use Luthier\Http\Response;

class Api implements IMiddleware
{
  public function handle(Request $request, Response $response, Closure $next): Response
  {
    $response->asJson();
    return $next($request, $response);
  }
}
