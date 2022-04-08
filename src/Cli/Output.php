<?php

namespace Luthier\Cli;

use Luthier\Cli\Colors\Colors;

class Output
{
  // Reset
  private static $clear = "\033[49m\033[0m";
  private static $defaultOptions = ["\033[0m", "\033[49m"];

  /**
   * Warning message colored in yellow
   */
  public static function warn($message)
  {
    $labelColors = [Colors::$textRed, Colors::$backgroundYellow];
    $separatorColors = [Colors::$textLightYellow, Colors::$backgroundClear];
    $messageColors = [Colors::$textLightYellow, Colors::$backgroundClear];
    self::center(self::bold(" WARNING "), "\n", $labelColors, "-", $separatorColors);
    self::output($message, "\n",  $messageColors);
    self::separator("-", $separatorColors);
  }

  /**
   * Danger message colored in red
   */
  public static function danger($message)
  {
    $labelColors = [Colors::$textYellow, Colors::$backgroundRed];
    $separatorColors = [Colors::$textRed, Colors::$backgroundRed];
    $messageColors = [Colors::$textRed, Colors::$backgroundClear];
    self::center(self::bold(" DANGER "), "\n", $labelColors, "-", $separatorColors);
    self::output($message,  "\n", $messageColors);
    self::separator("-", $separatorColors);
  }

  /**
   * Error message colored in bright red
   */
  public static function error(mixed $code = "")
  {
    $separatorColors = [Colors::$textRed, Colors::$backgroundClear];
    $labelColors = [Colors::$textRed, Colors::$backgroundClear];
    self::center(self::bold(" ERROR $code "),  "\n", $labelColors);
    self::separator("-", $separatorColors);
  }

  /**
   * Success message colored in bright green
   */
  public static function success($message)
  {
    $labelColors = [Colors::$textGreen, Colors::$backgroundClear];
    $separatorColors = [Colors::$textGreen, Colors::$backgroundBlack];
    $messageColors = [Colors::$textGreen, Colors::$backgroundClear];
    self::center(self::bold(" SUCCESS "), "\n", $labelColors);
    self::output($message, "\n", $messageColors);
    self::separator("-", $separatorColors);
  }

  // >>>>>>>>>>>>> PRIVATE METHODS

  /**
   * Output a message (colored or not)
   */
  public static function output($message, string $eol, ?array $messageOptions = null)
  {
    // Default options
    self::paint($messageOptions);
    echo $message . "$eol";
    echo self::$clear;
  }

  /**
   * Centers the message between a chain of characters
   */
  public static function center($message, string $eol, ?array $messageOptions = null, $filler = " ", ?array $fillerOptions = null)
  {
    // Compensates for the filler width
    $width = self::getTerminalWidth();
    $repeat = floor($width / strlen($filler));
    $messageSpace = floor(strlen($message) / 2);
    $half = floor(($repeat / 2) - ($messageSpace / 2));
    $printed = false;

    // Color line
    echo "$eol";
    self::paint($fillerOptions);
    // Print line
    for ($col = 1; $col <= $half; $col++) {
      echo $filler;
      // After completing the first half of fillers it shows the message. It happens only in the first time
      if ($col == $half && !$printed) {
        // Print message
        self::output($message,  $eol, $messageOptions);
        $printed = true;
        $col = 0;
        // Color line again
        self::paint($fillerOptions);
      }
    }
    echo self::$clear . "$eol";
  }

  /**
   * Anima um trecho de texto caractere por caractere centralizado. $colors é um array com [Colors::$fontColor, Colors::$backgroundColor]
   */
  public static function charByChar(string $message, $ms, array $colors)
  {

    $chars = str_split($message);
    $size = strlen($message);
    $string = "";

    $colors = $colors ?? self::$defaultOptions;

    foreach ($chars as $index => $char) {
      $string .= $char;
      if ($index !== $size - 1) {
        Output::center($string . $char, "\r", $colors);
      } else {
        Output::center($string, "", $colors);
      }
      time_nanosleep(0, $ms * 1000000);
    }

    echo "\n";
  }

  /**
   * Creates a line with the specified string
   */
  public static function separator(?string $string = null, ?array $options = null)
  {
    $string = $string ?? " ";
    $width = self::getTerminalWidth();
    $repeat = floor($width / strlen($string));
    // Print separator
    echo "\n";
    self::paint($options);
    for ($col = 0; $col < $repeat; $col++) {
      echo $string;
    }
    echo self::$clear . "\n";
  }

  /**
   * Based on tput output
   */
  public static function getTerminalWidth()
  {
    return exec('tput cols');
  }

  public static function bold($content)
  {
    return "\033[1m" . $content . "\033[0m";
  }

  public static function paint($options)
  {
    $options = $options ?? self::$defaultOptions;;
    echo $options[0] . $options[1];
  }
}
