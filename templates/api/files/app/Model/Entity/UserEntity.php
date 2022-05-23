<?php

namespace App\Model\Entity;

class UserEntity
{
  private string $name;
  private string $email;
  private int $age;

  public function __construct(string $name, string $email, int $age)
  {
    $this->name = $name;
    $this->email = $email;
    $this->age = $age;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getEmail()
  {
    return $this->email;
  }

  public function getAge()
  {
    return $this->age;
  }
}
