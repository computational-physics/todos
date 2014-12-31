<?php 
require('TodoDB.php');

// from json to sqlite3
$jsons = scandir('data/');
foreach ($jsons as $json) {
    if (substr($json, -1) != 'n') {
        continue;
    }
    $oldpath = 'data/' . $json;
    $user = substr($json, 0, -5);
    $content = file_get_contents($oldpath);
    $todos = json_decode($content);

    // insert
    $db = new TodoDB($user);
    foreach ($todos as $todo) {
        $todo = (array)$todo;
        $todo['create_date'] = $todo['date'];
        $todo['due_date'] = (isset($todo['due']))?$todo['due']:null;
        if ($todo['completed'] == 1) {
            $todo['completed'] = 'true';
        }
        else { 
            $todo['completed'] = 'false';
            echo $user . ': ' . $todo['title'] . '
';
        }
        if (!isset($todo['finish_date'])) $todo['finish_date'] = null;
        $rowid = $db->insert($todo);
        echo $rowid . '
';
    }
}
?>