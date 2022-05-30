<?php

include_once 'scripts/functions.php';
include_once 'scripts/data_arrays.php';
include_once 'scripts/Form_interaction.php';

if ($_POST) // Обработка запроса на добавление новой записи
{
  if (!isset($_POST["task_id"])) // Если запрос на нередактирование
  {
    $My_form = new Form_interaction($_POST, $types, $durations);

    if ($My_form->save())
    {
      $message = "<div style='color:green;'>Задача добавлена!</div>";
    }
    else
    {
      $message = $My_form->get_errors();
    }
  }
  else // иначе
  {
    $My_form = new Form_interaction($_POST, $types, $durations);

    if ($My_form->update($_POST["task_id"], (isset($_POST["status"]) ? 2 : 1)))
    {
      $message = "<div style='color:green;'>Данные задачи обновлены!</div>";
    }
    else
    {
      $message = $My_form->get_errors();
    }
  }

  $_POST = null;
  $data_relevance = false;
  
}

if ($_GET)  // Обработка запроса на фильтрацию записей
{
  $query = "SELECT * FROM `tasks`";

  if (isset($_GET["day"]))
  {
    $query =  Database::add_condition($query, "date = '" . $_GET["day"] . "'");
  }
  else if (isset($_GET["date"]))
  {
    if ($_GET["date"] == "all") {}
    else if ($_GET["date"] == "this_week") { $query =  Database::add_condition($query, "date_format(date, '%y-%m-%d') between date_format(now() - interval (DAYOFWEEK(now()) -2) day, '%y-%m-%d') and date_format(now() + interval (7 - (DAYOFWEEK(now()) -1)) day, '%y-%m-%d')"); }
    else if ($_GET["date"] == "next_week") { $query =  Database::add_condition($query, "date_format(date, '%y-%m-%d') between date_format(now() - interval (DAYOFWEEK(now()) -2) day + interval 7 day, '%y-%m-%d') and date_format(now() + interval (7 - (DAYOFWEEK(now()) -1)) day + interval 7 day, '%y-%m-%d')"); }
    else if ($_GET["date"] == "this_month") { $query =  Database::add_condition($query, "date_format(date, '%y-%m-%d') between date_format(now() - interval (DAY(now()) - 1) day, '%y-%m-%d') and date_format(LAST_DAY(now()), '%y-%m-%d')"); }
    else if ($_GET["date"] == "next_month") { $query =  Database::add_condition($query, "date_format(date, '%y-%m-%d') between date_format(now() + interval 1 month - interval (DAY(now()) - 1) day, '%y-%m-%d') and date_format(LAST_DAY(now() + interval 1 month), '%y-%m-%d')"); }
  }

  if (isset($_GET["status"]))
  {
    if ($_GET["status"] == "now") { $query = Database::add_condition($query, "status = '1'"); }
    else if ($_GET["status"] == "completed") { $query = Database::add_condition($query, "status = '2'"); }
    else if ($_GET["status"] == "over") {
      $query = Database::add_condition($query, "status = '1' AND (DATEDIFF(date, date_format(now(), '%y-%m-%d')) < 0 OR " .
        "(DATEDIFF(date, date_format(now(), '%y-%m-%d')) = 0 AND TIMEDIFF(time, date_format(now(), '%H:%i')) < 0));");
    }
  }
  
  $data = DATABASE::exec($query);
  $data_relevance = true;
}

if (!$data_relevance)  // Получение данных из БД
{
  $data = Form_interaction::load_all();
  $data_relevance = true;
}

?>

<!DOCTYPE html>
<html lang="ru" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  	<link rel="stylesheet" href="styles/style.css">
    <title>Мой календарь</title>
  </head>
  <body>
    <div class="messages">
      <?php if ($message) { echo $message; }?>
    </div>
    <div class="main_cont">
      <h1 class="main_cont_header">Мой календарь</h1>
      <div class="task_cont">
        <h3 class="task_cont_header">Новая задача</h3>
        <div class="form_cont">
          <form method="POST" action="" class="form_cont_form">
            <div class="form_cont_field">
              <label class="form_cont_label_header">Тема:</label>
              <input class="form_cont_input_item" type="text" name="topic" value="<?= __clear($_POST['topic'] ?? '') ?>">
            </div>
            <div class="form_cont_field">
              <label class="form_cont_label_header">Тип:</label>
              <select class="form_cont_input_select" name="type">
                <?php
                  foreach ($types as $type) {
                    echo '<option' . (strcmp($type["name"], ($_POST['type'] ?? '')) ? '' : ' selected') . '>' . $type["name"] . '	</option>';
                  }
                ?>
              </select>
            </div>
            <div class="form_cont_field">
              <label class="form_cont_label_header">Место:</label>
              <input class="form_cont_input_item" type="text" name="place" value="<?= __clear($_POST['place'] ?? '') ?>">
            </div>
            <div class="form_cont_field">
              <label class="form_cont_label_header">Дата и время:</label>
              <input type="date" name="date" value="<?= __clear($_POST['date'] ?? '') ?>" class="form_cont_field_input_date">
              <input type="time" name="time" value="<?= __clear($_POST['time'] ?? '') ?>">
            </div>
            <div class="form_cont_field">
              <label class="form_cont_label_header">Длительность:</label>
              <select class="form_cont_input_select" name="duration">
                <?php
                  foreach ($durations as $duration) {
                    echo '<option' . (strcmp($duration["name"], ($_POST['duration'] ?? '')) ? '' : ' selected') . '>' . $duration["name"] . '	</option>';
                  }
                ?>
              </select>
            </div>
            <div class="form_cont_field">
              <label class="form_cont_label_header">Описание:</label>
              <textarea name="comment" cols="55" rows="7" placeholder="Введите сюда описание к новой задаче"><?= __clear($_POST['comment'] ?? '');?></textarea>
            </div>

            <button class="form_cont_button" type="submit">Добавить</button>
          </form>
        </div>
      </div>
      <div class="list_cont">
        <h3 class="list_cont_header">Список задач</h3>
        <div class="list_cont_menu">
          <span>Фильтры: </span>
          <select class="element_for_filter" name="sort_by_status">
            <?php foreach ($status_filter as $key => $value) { ?>
            <?php echo "<option value='" . $key . "'" . (strcmp(($_GET['status'] ?? ''), $key) ? '' : ' selected') . ">" . $value . "</option>"; ?>
            <?php } ?>
          </select>
          <input class="element_for_filter sort_by_date" type="date" value="<?php echo $_GET['day'] ?? '' ?>">
          <?php foreach ($date_filter as $key => $value) { ?>
          <?php echo "<span class='element_for_filter' value='" . $key . "'" . (strcmp(($_GET['date'] ?? ''), $key) ? '' : ' style="color: #153e9b; text-decoration: none;"') . ">" . $value . "</span>"; ?>
          <?php } ?>
        </div>
        <div class="list_cont_tasks">
          <table>
            <thead>
              <tr>
                <th>Тип</th>
                <th>Задача</th>
                <th>Описание</th>
                <th>Место</th>
                <th>Дата и время</th>
                <th>Длительность</th>
                <th>Статус</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($data as $key => $value) { ?>
              <tr>
                <td <?= __clear("data__id=" . $value["id"]); ?>><?= __clear($types[$value["type"] - 1]["name"]); ?></td>
                <td class="list_cont_tasks_td_taskName"><?= __clear($value["topic"]); ?></td>
                <td><?= __clear($value["comment"]); ?></td>
                <td><?= __clear($value["place"]); ?></td>
                <td><?= __clear($value["date"] . " " . $value["time"]); ?></td>
                <td><?= __clear($durations[$value["duration"] - 1]["name"]); ?></td>
                <td><?= __clear($statuses[$value["status"] - 1]["name"]); ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <script src="script.js"></script>

  </body>
</html>