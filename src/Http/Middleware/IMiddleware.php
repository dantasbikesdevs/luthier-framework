<?php

use Luthier\Http\Request;
use Luthier\Http\Response;

interface IMiddleware
{
  public function handle(Request $request, Closure $next): Response;
}
