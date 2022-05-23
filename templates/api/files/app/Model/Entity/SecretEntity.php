<?php

namespace App\Model\Entity;

class SecretEntity
{
  private string $title;
  private string $message;

  public function __construct(string $title, string $message)
  {
    $this->title = $title;
    $this->message = $message;
  }

  public function getTitle()
  {
    return $this->title;
  }

  public function getMessage()
  {
    return $this->message;
  }
}
