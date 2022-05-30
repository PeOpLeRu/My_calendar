<?php

class Database
{
  static protected $pdo = null;

  static public function get_connection() : PDO
  {
    if (static::$pdo == null)
    {
      $config = include 'config.php';
      static::$pdo = new PDO("mysql:host=127.0.0.1;dbname=task_calendar94;", "task_calendar94", "5jEQRM60");//new PDO(("mysql:host=" . $config['db_host'] . ";dbname=" . $config['db_name'] . ";"), $config['db_user'], $config['db_pass']);
    }

    return static::$pdo;
  }

  static public function exec($sql, $sql_params=null)
  {
    if ($sql_params)
    {
      $query = static::get_connection()->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

      $query->execute($sql_params);
    }
    else {
      $query = static::get_connection()->prepare($sql);
      $query->execute();
    }

    return $query->fetchAll();
  }

  static public function add_condition($sql_query, $sql_cond)
  {
    $more_one = false;
    if (strpos($sql_query, "WHERE")) { $more_one = true; } else { $sql_query .= " WHERE "; }
    if ($more_one) { $sql_query .= " AND "; }

    $sql_query .= $sql_cond;

    return $sql_query;
  }

}

?>