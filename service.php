<?php 
require('TodoDB.php');
session_start();
try {
    $t_user = $_SESSION['app_user_name'];
    // if junfan, assign user as given
    if ($t_user == 'Junfan' && isset($_POST['t_user'])) {
        $t_user = $_POST['t_user'];
    } 
    $db = new TodoDB($t_user);
    
    header('Content-type: application/json');
    if ($_POST['q'] == 'check') {
        if (intval($_POST['version']) < $db->queryVersion()) {
            echo '{"hasUpdate": true}';
        } else {
            echo '{"hasUpdate": false}';
        }
        exit();
    }
    if ($_POST['q'] == 'get') {
        $todos = $db->queryAll();
        $version = $db->queryVersion();
        echo json_encode((object) array(
            'todos' => $todos,
            'version' => $version
        ) );
        exit();
    }
    if ($_POST['q'] == 'add') {
        $rowid = $db->insert($_POST['todo']);
        $version = $db->queryVersion();
        echo json_encode((object) array(
            'rowid' => $rowid,
            'version' => $version
        ) );
        exit();
    }
    if ($_POST['q'] == 'update') {
        $success = $db->update($_POST['todo']);
        $version = $db->queryVersion();
        echo json_encode((object) array(
            'success' => $success,
            'version' => $version
        ) );
        exit();
    }
    if ($_POST['q'] == 'delete') {
        $success = $db->delete($_POST['todo']);
        $version = $db->queryVersion();
        echo json_encode((object) array(
            'success' => $success,
            'version' => $version
        ) );
        exit();
    }
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
}
?>