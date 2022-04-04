<?php

namespace Luthier\Http\Middleware;

use Closure;
use IMiddleware;
use Luthier\Http\Request;
use Luthier\Http\Response;

class Api implements IMiddleware
{
  public function handle(Request $request, Closure $next): Response
  {
    $request->json();
    return $next($request);
  }
}
