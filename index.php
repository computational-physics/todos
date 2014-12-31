<?php
$user_name;
$usermail_path = '/etc/usermail.list';
// debug
include('debug.php');
if ($debug) {
	session_start();
    $user_name = 'Junfan';
    $_SESSION['app_user_name'] = $user_name;
	$usermail_path = 'usermail.list';
} else {
	require_once('../auth.php');
	$user_name = combo_app_auth('todos');
}
?><!doctype html>
<html lang="en" data-framework="angularjs">
	<head>
		<meta charset="utf-8">
		<title>Ourphysics • Todo</title>
		<link rel="stylesheet" href="css/base.css">
		<link rel="stylesheet" href="bower_components/ngQuickDate/dist/ng-quick-date.css">
		<style>[ng-cloak] { display: none; }</style>
		<script type="text/javascript">
		<?php if (strpos($user_name, '.') !== false) {echo 'alert("please log in wiki first.");';} ?>
		</script>
	</head>
	<body ng-app="todomvc">
		<ng-view />

		<script type="text/ng-template" id="todomvc-index.html">
			<section id="todoapp">
				<header id="header">
					<h1>todos <small>1.1</small>
						<select ng-change="changeUser()" ng-model="targetUser" <?php if ($user_name != 'Junfan') echo 'style="display: none"'; ?> >
						<?php
							$users = array();
							$lines = explode("\n", file_get_contents($usermail_path));
							foreach ($lines as $line) {
								if (substr($line, 0, 1) == "#") {
									// comment
								} else {
									$tokens = explode(" ", $line);
									if ($tokens[0] != "")
										array_push($users, $tokens[0]);
								}
							}
							foreach ($users as $user) {
								echo "<option " . (($user == 'Junfan')? "selected":"") . " value='" . $user . "'>" . $user . "</option>";
							}
						?>
						</select>
					</h1>
					<div>
						<form id="todo-form" ng-model="newForm" ng-submit="addTodo()">
							<input id="new-todo" placeholder="What needs to be done?" ng-model="newTodo" autofocus style="width: 70%"></input>
							due on <quick-datepicker disable-timepicker="true" date-format="yyyy-M-d" time-format="" id="new-due" ng-model="newDue"></quick-datepicker>
							<input type="submit" style="position: absolute; left: -9999px;"/>
						</form>
					</div>
				</header>
				<section id="main" ng-show="todos.length" ng-cloak>
					<label for="toggle-all">Mark all as complete</label>
					<ul id="todo-list">
						<li ng-repeat="todo in todos | orderBy:['completed', '-priority', 'rowid'] | filter:statusFilter track by $index " ng-class="{completed: todo.completed, editing: todo == editedTodo}">
							<div class="view">
								<select class="flag" ng-model="todo.priority" ng-change='pushTODO(todo)'>
									<option value='1'>1</option>
									<option value='2'>2</option>
									<option value='3'>3</option>
									<option value='4'>4</option>
									<option value='5'>5</option>
									<option value='6'>6</option>
									<option value='7'>7</option>
									<option value='8'>8</option>
									<option value='9'>9</option>
								</select>
								<input class="toggle" type="checkbox" ng-model="todo.completed" ng-change="updateFinishDate(todo)">
								<div>
									<label ng-dblclick="editTodo(todo)" style="display: inline-block; width: 50%">{{todo.title}}</label>
									<label class="date">{{todo.create_date}}</label>
									<label ng-show="todo.completed" class="finish-date">{{todo.finish_date}}</label>
									<label class="due" ng-dblclick="editTodo(todo)" style="display: inline-block; margin-left: 0">{{todo.due_date}}</label>
								</div>
								<button class="mail" ng-click="mailTodo(todo)"></button>
								<button class="destroy" ng-click="removeTodo(todo)"></button>
							</div>
							<form ng-submit="doneEditing(todo)" style="margin-bottom: 0">
								<div class="edit" todo-escape="revertEditing(todo)" ng-blur="doneEditing(todo)">
									<input ng-trim="false" ng-model="tempTodo.title" todo-focus="todo == editedTodo"></input>
									<small>due on </small><quick-datepicker disable-timepicker="true" date-format="yyyy-M-d" time-format="" ng-model="tempTodo.due_date"></quick-datepicker>
									<input type="submit" style="position: absolute; left: -9999px;"/>
								</div>
							</form>
						</li>
					</ul>
				</section>
				<footer id="footer" ng-show="todos.length" ng-cloak>
					<span id="todo-count"><strong>{{remainingCount}}</strong>
						<ng-pluralize count="remainingCount" when="{ one: 'item left', other: 'items left' }"></ng-pluralize>
					</span>
					<ul id="filters">
						<li>
							<a ng-class="{selected: status == ''} " href="#/">Recent</a>
						</li>
						<li>
							<a ng-class="{selected: status == 'archived'}" href="#/archived">Archived</a>
						</li>
					</ul>
					<button id="clear-completed" ng-click="clearCompletedTodos()" ng-show="completedCount">Clear completed ({{completedCount}})</button>
				</footer>
			</section>
			<footer id="info">
				<p>Double-click to edit a todo</p>
				<p>This work is based on the project TodoMVC:</p>
				<p>Credits:
					<a href="http://twitter.com/cburgdorf">Christoph Burgdorf</a>,
					<a href="http://ericbidelman.com">Eric Bidelman</a>,
					<a href="http://jacobmumm.com">Jacob Mumm</a> and
					<a href="http://igorminar.com">Igor Minar</a>
				</p>
			</footer>
			<div id="message-box"></div>
		</script>
		<script src="bower_components/angular/angular.min.js"></script>
		<script src="bower_components/angular-route/angular-route.min.js"></script>
		<script src="js/app.js"></script>
		<script src="js/controllers/todoCtrl.js"></script>
		<script src="js/services/todoStorage.js"></script>
		<script src="js/services/todoMailer.js"></script>
		<script src="js/directives/todoFocus.js"></script>
		<script src="js/directives/todoEscape.js"></script>
		<script src="bower_components/ngQuickDate/dist/ng-quick-date.min.js"></script>
	</body>
</html>
