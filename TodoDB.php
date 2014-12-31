<?php
class TodoDB extends SQLite3
{
  function __construct($user)
  {
    date_default_timezone_set('Asia/Shanghai');
    $dbfile = 'data/' . $user . '.todos';
    $this->open($dbfile, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    // initialize database
    $results = $this->query("SELECT name FROM SQLITE_MASTER WHERE type = 'table'");
    if (!$results->fetchArray()) {
        // create table
        $results = $this->exec('CREATE TABLE todos
                    (title TEXT, create_date TEXT, due_date TEXT,
                    finish_date TEXT, completed INTEGER,
                    priority INTEGER)');
        $results = $this->exec('CREATE TABLE version
                    (version INTEGER)');
        $results = $this->exec('INSERT INTO version VALUES (0)');
    }
  }

  function queryVersion() {
    $results = $this->query('SELECT version FROM version');
    $row = $results->fetchArray();
    return $row['version'];
  }

  function incrementVersion() {
    $version = $this->queryVersion() + 1;
    $results = $this->exec('UPDATE version SET version = ' . $version);
  }

  function queryAll() {
    $results = $this->query('SELECT ROWID, * FROM todos');
    $todos = array();
    while ($row = $results->fetchArray()) {
        foreach ($row as $key => $value) {
            if (is_numeric($key)) {
                unset($row[$key]);
            }
        }
        if ($row['completed'] == 1) $row['completed'] = true;
        if ($row['completed'] == 0) $row['completed'] = false;
        array_push($todos, $row);
    }
    return $todos;
  }

  function insert($todo) {
    $stmt = $this->prepare('INSERT INTO todos VALUES (:title, :create_date,
        :due_date, :finish_date, :completed, :priority)');
    $stmt->bindValue(':title', $todo['title'], SQLITE3_TEXT);
    $stmt->bindValue(':create_date', $todo['create_date'], SQLITE3_TEXT);
    $stmt->bindValue(':due_date', $todo['due_date'], SQLITE3_TEXT);
    $stmt->bindValue(':finish_date', $todo['finish_date'], SQLITE3_TEXT);
    if ($todo['completed'] == 'true') $todo['completed'] = 1;
    if ($todo['completed'] == 'false') $todo['completed'] = 0;
    $stmt->bindValue(':completed', $todo['completed'], SQLITE3_INTEGER);
    $stmt->bindValue(':priority', $todo['priority'], SQLITE3_INTEGER);
    $results = $stmt->execute();
    if ($results != false) {
        $this->incrementVersion();
        return $this->querySingle('SELECT rowid FROM todos ORDER BY 
            rowid DESC LIMIT 1');
    } else {
        return false;
    }
  }

  function update($todo) {
    $stmt = $this->prepare('UPDATE todos SET title = :title, 
        create_date = :create_date, due_date = :due_date, 
        finish_date = :finish_date, completed = :completed, 
        priority = :priority WHERE rowid = :rowid');
    $stmt->bindValue(':title', $todo['title'], SQLITE3_TEXT);
    $stmt->bindValue(':create_date', $todo['create_date'], SQLITE3_TEXT);
    $stmt->bindValue(':due_date', $todo['due_date'], SQLITE3_TEXT);
    $stmt->bindValue(':finish_date', $todo['finish_date'], SQLITE3_TEXT);
    if ($todo['completed'] == 'true') $todo['completed'] = 1;
    if ($todo['completed'] == 'false') $todo['completed'] = 0;
    $stmt->bindValue(':completed', $todo['completed'], SQLITE3_INTEGER);
    $stmt->bindValue(':priority', $todo['priority'], SQLITE3_INTEGER);
    $stmt->bindValue(':rowid', $todo['rowid'], SQLITE3_INTEGER);
    $results = $stmt->execute();
    if ($results != false) {
        $this->incrementVersion();
        return true;
    } else {
        return false;
    }
  }

  function delete($rowid) {
    $stmt = $this->prepare('DELETE FROM todos WHERE rowid = :rowid');
    $stmt->bindValue(':rowid', $rowid, SQLITE3_INTEGER);
    $results = $stmt->execute();
    if ($results != false) {
        $this->incrementVersion();
        return true;
    } else {
        return false;
    }
  }
}
?>