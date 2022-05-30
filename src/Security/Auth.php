<?php

namespace Luthier\Security;

use App\Repositories\UserRepository;

class Auth {
  public static function authJWT($payload) {
    $user = (new UserRepository)->getUserJWT($payload);
    return $user;
  }
}
