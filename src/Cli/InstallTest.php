<?php

namespace Luthier\Cli;

use Luthier\Cli\Colors\Colors;

class InstallTest
{
  public static function pest()
  {
    system("composer require pestphp/pest --dev --with-all-dependencies");
    system("./vendor/bin/pest --init");
  }

  public static function phpUnit()
  {
    echo Colors::redText("Instalação não preparada ainda! Recomendamos usar o PEST por enquanto.");
  }

  public static function codeception()
  {
    echo Colors::redText("Instalação não preparada ainda! Recomendamos usar o PEST por enquanto.");
  }
}
