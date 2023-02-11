<?php

$to_do = array();
$done = array();

$status = $status ?? null;
$q = getDB()->prepare("SELECT * FROM todos WHERE class_id=:class_id AND duedate >= :min_date ORDER BY duedate DESC");
$q->execute([
    'class_id' => $status['class_id'],
    'min_date' => date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 30) // 30 days ago
]);

while ($todo = $q->fetch()) {
    $q2 = getDB()->prepare("SELECT status FROM status WHERE todo_id=:todo_id AND user_id=:user_id LIMIT 1");
    $q2->execute([
        'todo_id' => $todo['id'],
        'user_id' => $status['id']
    ]);

    $split = preg_split("/\r\n|\n|\r/", $todo['content']);
    $todo['title'] = $split[0];
    $todo['description'] = implode('<br>', array_slice($split, 1));

    $row = $q2->fetch();
    $todo['status'] = $row ? TodoStatus::fromName($row['status']) : TodoStatus::TODO;
    if ($todo['status'] == TodoStatus::DONE) {
        $done[] = $todo;
    } else {
        $to_do[] = $todo;
    }
}
function print_todo(array $todos): void
{
    foreach ($todos as $todo) {
        ?>
        <div class="heading">
            <div class="subject">
                <p><?= $todo['subject'] ?></p>
            </div>
            <div class="duedate">
                <p><?= duedate_to_str($todo['duedate']) ?></p>
            </div>
            <div class="status">
                <p><?= $todo['status']->value ?></p>
            </div>
            <div class="img-button edit">
                <img alt="Éditer" src="<?= getRootPath() ?>todo/svg/edit.svg"/>
            </div>
        </div>
        <div class="content">
            <div class="img-button link <?= $todo['link'] ? '' : 'disabled' ?>">
                <img alt="Lien associé" src="<?= getRootPath() ?>todo/svg/link.svg"/>
            </div>
            <div class="description">
                <p class="first-line"><?= $todo['title'] ?></p>
                <p><?= $todo['description'] ?></p>
            </div>
        </div>
        <?php
    }
}

?>
<h3>À faire :</h3>
<div class="todo-list">
    <?php
    print_todo($to_do);
    ?>
</div>

<h3>Fait :</h3>
<div class="todo-list">
    <?php
    print_todo($done);
    ?>
</div>