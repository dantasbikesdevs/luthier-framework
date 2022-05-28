<?php

namespace App\Models\Entity;

class Entity
{
  public function __construct($values)
  {
    foreach ($values as $key => $value) {
      $key = strtoupper($key);
      $this->$key = $value;
    }
  }
}
