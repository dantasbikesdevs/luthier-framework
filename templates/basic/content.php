<?php

function getPresetContent($filename)
{
  return file_get_contents(__DIR__ . "/files/$filename");
}
