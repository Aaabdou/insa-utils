<?php
function leave_class($id, $class_id): void
{
    require_once __DIR__ . '/../db.php';
    $q = getDB()->prepare("UPDATE users SET class_id=NULL WHERE id=:id AND class_id=:class_id");
    $r = $q->execute([":id" => $id, ":class_id" => $class_id]);
    if ($r) {
        // check class is not empty
        $q = getDB()->prepare("SELECT 1 FROM users WHERE class_id=:class_id LIMIT 1");
        $q->execute([":class_id" => $class_id]);
        if ($q->rowCount() == 0) {
            // delete class
            $q = getDB()->prepare("DELETE FROM classes WHERE id=:class_id");
            $q->execute([":class_id" => $class_id]);
        }
    }
}

function get_class_name($class_id)
{
    $q = getDB()->prepare("SELECT name FROM classes WHERE id=:id LIMIT 1");
    $q->execute([":id" => $class_id]);
    $class = $q->fetch();
    if ($class == null) {
        return null;
    } else {
        return $class['name'];
    }
}

enum TodoStatus: string
{
    case TODO = "À faire";
    case IN_PROGRESS = "En cours";
    case DONE = "Fait";

    public static function fromName($name): TodoStatus
    {
        if(!$name) return TodoStatus::TODO;
        return match (strtolower($name)) {
            "in_progress" => TodoStatus::IN_PROGRESS,
            "done" => TodoStatus::DONE,
            default => TodoStatus::TODO,
        };
    }
}



function duedate_to_str($duedate): string{
    $current_date = DateTime::createFromFormat('Y-m-d', (new DateTime())->format('Y-m-d'));
    $duedate = DateTime::createFromFormat('Y-m-d', $duedate);


    $dist = get_day_distance($duedate, $current_date);

    if($dist == 0) return "Aujourd'hui";

    if($dist == 1){
        return "Demain (" . format_date('EEEE', $duedate) . ')';
    }
    if($dist == 2){
        return "Après demain (" . format_date('EEEE', $duedate) . ')';
    }

    return format_date('EEEE d', $duedate) . " ($dist&nbsp;J)";
}
function format_date($format, $date): string{
    $fmt = new IntlDateFormatter('fr_FR');
    $fmt->setTimeZone(new DateTimeZone('Europe/Paris'));
    $fmt->setPattern($format);
    return ucfirst($fmt->format($date));
}
function get_day_distance($date1, $date2): int
{
    $diff = $date1->diff($date2);
    return $diff->days;
}