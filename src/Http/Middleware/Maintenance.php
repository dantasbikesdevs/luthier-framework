<?php

namespace Luthier\Http\Middleware;

use Closure;
use Luthier\Http\Middleware\IMiddleware;
use Luthier\Http\Request;
use Luthier\Http\Response;

class Maintenance implements IMiddleware
{

  public function handle(Request $request, Response $response, Closure $next): Response
  {

    if (getenv('MAINTENANCE') == 'true') {
      throw new \Exception('Página em manutenção', 501);
    }
    return $next($request, $response);
  }
}
