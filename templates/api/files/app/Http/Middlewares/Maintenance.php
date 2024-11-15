<?php declare(strict_types=1);

namespace App\Http\Middlewares;

use Closure;
use Luthier\Http\Middlewares\IMiddleware;
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
