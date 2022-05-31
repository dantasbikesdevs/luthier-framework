<?php

namespace App\Models\Entity;

class UserEntity extends Entity
{
  private readonly ?int $ID;
  public string $NAME;
  public string $EMAIL;
  protected string $PASSWORD;
  public int $AGE;
  protected ?array $PERMISSIONS;
  protected ?array $ROLES;

  public function __construct($values = null)
  {
    if ($values) {
      parent::__construct($values);
    }
  }

  public function getId()
  {
    return $this->ID;
  }

  public function getName()
  {
    return $this->NAME;
  }

  public function setName(string $name)
  {
    $this->NAME = empty($name) ? $this->NAME : $name;

    return $this;
  }

  public function getEmail()
  {
    return $this->EMAIL;
  }

  public function setEmail(string $email)
  {
    $this->EMAIL = empty($email) ? $this->EMAIL : $email;

    return $this;
  }

  public function getPassword()
  {
    return $this->PASSWORD;
  }

  public function setPassword(string $password)
  {
    $this->PASSWORD = empty($password) ? $this->PASSWORD : $password;

    return $this;
  }

  public function getAge()
  {
    return $this->AGE;
  }

  public function setAge(int $age)
  {
    $this->AGE = empty($age) ? $this->AGE : $age;

    return $this;
  }

  public function getRoles(){
    return $this->ROLES;
  }

  public function setRoles(?array $roles) {
    $this->ROLES = $roles;
  }

  public function getPermissions(){
    return $this->PERMISSIONS;
  }

  public function setPermissions(?array $permissions){
    $this->PERMISSIONS = $permissions;
  }
}
