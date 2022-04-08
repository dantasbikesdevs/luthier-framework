<?php

namespace Luthier\Cli\Colors;

class Colors
{
  // Normal text colors
  public static $textClear = "\033[0m";
  public static $textBlack = "\033[39m";
  public static $textWhite = "\033[97m";
  public static $textRed = "\033[31m";
  public static $textGreen = "\033[32m";
  public static $textYellow = "\033[33m";
  public static $textBlue = "\033[34m";
  public static $textMagenta = "\033[35m";
  public static $textCyan = "\033[36m";

  // Light  colors
  public static $textLightRed = "\033[91m";
  public static $textLightGreen = "\033[92m";
  public static $textLightYellow = "\033[93m";
  public static $textLightBlue = "\033[94m";
  public static $textLightMagenta = "\033[95m";
  public static $textLightCyan = "\033[96m";

  // Normal background colors
  public static $backgroundClear = "\033[49m";
  public static $backgroundBlack = "\033[40m";
  public static $backgroundWhite = "\033[107m";
  public static $backgroundRed = "\033[41m";
  public static $backgroundGreen = "\033[42m";
  public static $backgroundYellow = "\033[43m";
  public static $backgroundBlue = "\033[44m";
  public static $backgroundMagenta = "\033[45m";
  public static $backgroundCyan = "\033[46m";

  // Light background colors
  public static $backgroundLightRed = "\033[101m";
  public static $backgroundLightGreen = "\033[102m";
  public static $backgroundLightYellow = "\033[103m";
  public static $backgroundLightBlue = "\033[104m";
  public static $backgroundLightMagenta = "\033[105m";
  public static $backgroundLightCyan = "\033[106m";

  public static function blackText(string $text)
  {
    return self::$textBlack . $text . self::$textClear;
  }

  public static function whiteText(string $text)
  {
    return self::$textWhite . $text . self::$textClear;
  }

  public static function greenText(string $text)
  {
    return self::$textGreen . $text . self::$textClear;
  }

  public static function yellowText(string $text)
  {
    return self::$textYellow . $text . self::$textClear;
  }

  public static function blueText(string $text)
  {
    return self::$textBlue . $text . self::$textClear;
  }

  public static function magentaText(string $text)
  {
    return self::$textMagenta . $text . self::$textClear;
  }

  public static function cyanText(string $text)
  {
    return self::$textCyan . $text . self::$textClear;
  }

  public static function redText(string $text)
  {
    return self::$textRed . $text . self::$textClear;
  }
}
