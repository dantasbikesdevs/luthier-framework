<?php

namespace Luthier;

require_once(realpath(dirname(__FILE__, 2) . '/Config/config.php'));

use \Luthier\Http\Middleware\Queue as Middleware;

class Luthier
{
  public static function init()
  {
    // MAPEAMENTO DOS MIDDLEWARES
    Middleware::setMap([
      'is' => \Luthier\Http\Middleware\Is::class,
      'api' => \Luthier\Http\Middleware\Api::class,
      'can' => \Luthier\Http\Middleware\Can::class,
      'cache' => \Luthier\Http\Middleware\Cache::class,
      'jwtAuth' => \Luthier\Http\Middleware\JWTAuth::class,
      'maintenance' => \Luthier\Http\Middleware\Maintenance::class,
      'checkLogged' => \Luthier\Http\Middleware\CheckLogged::class,
      'userBasicAuth' => \Luthier\Http\Middleware\UserBasicAuth::class,
      'authenticatedUser' => \Luthier\Http\Middleware\AuthenticatedUser::class,
      'authenticatedAdmin' => \Luthier\Http\Middleware\AuthenticatedAdmin::class,
    ]);

    //MIDDLEWARES PADRÕES (EXECUTADOS EM TODAS AS ROTAS)
    Middleware::setDefault([
      'maintenance',
    ]);
  }
}
