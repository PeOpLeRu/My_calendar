<?php

function __clear(string $value='')
{
   return htmlspecialchars($value);
 }

function convert_to_index(string $search_value, array $collection) : int
{
  $count = 1;

  foreach ($collection as $key => $value)
  {
    if (strcmp($value["name"], $search_value))
    {
      $count += 1;
    }
    else
    {
      break;
    }
  }

  if ($count > count($collection))
  {
    return 0;
  }

  return $count;
}

 ?>