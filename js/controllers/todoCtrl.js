/*global angular */

/**
 * The main controller for the app. The controller:
 * - retrieves and persists the model via the todoStorage service
 * - exposes the model to the template and provides event handlers
 */
Date.prototype.getStdString = function() {
	function to2digits(input) {
		if (input < 10) return '0' + input;
		else return input;
	}
	return this.getFullYear() + '-' + to2digits(this.getMonth() + 1) + 
	'-' + to2digits(this.getDate());
};
angular.module('todomvc')
	.controller('TodoCtrl', function TodoCtrl($scope, $routeParams, $filter, todoStorage, $interval, $timeout, todoMailer) {
		'use strict';

		$scope.targetUser = "Junfan";
		$scope.currentVersion = -1; // default version, means empty

		$scope.todos = [];
		$scope.originalTodos = [];

		$scope.isArchived = false;

		function checkUpdate() {
			if ($scope.editedTodo != null) return;
			todoStorage.checkUpdate($scope.targetUser, $scope.currentVersion)
			.success(function(response) {
				if (response.hasUpdate) getTODOS();
			})
			.error(function(error) {
				$scope.notify("failed to check updates.");
			});
		}

		function getTODOS() {
			if ($scope.editedTodo != null) return;
			if ($scope.isArchived) {
				todoStorage.getArchivedTODOS($scope.targetUser)
				.success(function(response) {
					$scope.currentVersion = response.version;
					$scope.todos = response.todos;
					$scope.notify("archives fetched :-)");
				})
				.error(function(error) {
					$scope.notify("failed to get todos.");
				});
			} else {
				todoStorage.getTODOS($scope.targetUser)
				.success(function(response) {
					$scope.currentVersion = response.version;
					$scope.todos = response.todos;
					$scope.notify("todos fetched :-)");
				})
				.error(function(error) {
					$scope.notify("failed to get todos.");
				});
			}
		}

		// push the modification of todo to server
		$scope.pushTODO = function (todo) {
			todoStorage.updateTODO($scope.targetUser, todo)
			.success(function (response) {
				$scope.currentVersion = response.version;
			})
			.error(function(error) {
				window.alert('The modification you made fails to sync with server due to some unknown reasons, sorry to hear that.');
			});
		};

		checkUpdate();

		// check update every 3 secs
		var updateInterval;

		updateInterval = $interval(function() {
        	checkUpdate();
      	}, 3000);

      	$scope.$on("$destroy", function(){
	        $interval.cancel(updateInterval);
	    });

		$scope.newTodo = '';
		$scope.editedTodo = null;


		// adjust priority and count
		$scope.$watch('todos', function (newValue, oldValue) {
			for (var i = 0; i < $scope.todos.length; ++i) {
				if (typeof($scope.todos[i].priority) != 'number') 
					$scope.todos[i].priority = parseInt($scope.todos[i].priority);
			}
			$scope.remainingCount = $filter('filter')($scope.todos, { completed: false }).length;
			$scope.completedCount = $scope.todos.length - $scope.remainingCount;
			$scope.allChecked = !$scope.remainingCount;
		}, true);

		$scope.notify = function(message) {
			var box = document.getElementById('message-box');
			box.innerText = message;
			box.className = "show";
			window.setTimeout(function() {
				var box = document.getElementById('message-box');
				box.className = "";
			}, 2000);
		}

		$scope.mailTodo = function(todo) {
			todoMailer.send(todo, $scope.targetUser)
				.success(function(user) {
					$scope.notify("notified " + user + ".");
				});
		}

		$scope.changeUser = function() {
			$scope.currentVersion = -1;
			checkUpdate();
		}

		$scope.updateFinishDate = function(todo) {
			var index = $scope.todos.indexOf(todo);
			$scope.todos[index].finish_date = $scope.getDateString();
			$scope.pushTODO($scope.todos[index]);
		}

		$scope.getDateString = function() {
			var dateIn = new Date();
			return dateIn.getStdString();
		}

		// Monitor the current route for changes and adjust the filter accordingly.
		$scope.$on('$routeChangeSuccess', function () {
			var status = $scope.status = $routeParams.status || '';
			if (status == 'archived') {
				$scope.isArchived = true;
				getTODOS();
			} else {
				// back to '/'
				window.location.hash = '/';
			}
		});

		$scope.addTodo = function () {
			
			var newDate = $scope.getDateString();
			var newTodo = $scope.newTodo.trim();
			var newDue = ($scope.newDue)?$scope.newDue.getStdString():null;
			if (!newTodo.length) {
				return;
			}

			var todoObj = {
				'title': newTodo,
				'create_date': newDate,
				'due_date': newDue,
				'finish_date': null,
				'priority': 1,
				'completed': false
			};
			todoStorage.addTODO($scope.targetUser, todoObj)
			.success(function(response) {
				$scope.currentVersion = response.version;
				todoObj.rowid = response.rowid;
				if (todoObj.rowid) {
					$scope.todos.push(todoObj);
					$scope.notify("New todo is at bottom.");
				}
				else {
					$scope.notify("failed to add todo.");
				}
			}).error(function(error) {
				$scope.notify("failed to add todo.");
			});


			//$scope.todos = todos;


			$scope.newTodo = '';
			$scope.newDue = '';

		};

		$scope.editTodo = function (todo) {
			$scope.editedTodo = todo;
			// Clone the original todo to restore it on demand.
			$scope.originalTodo = angular.extend({}, todo);
			$scope.tempTodo = angular.extend({}, todo);
		};

		$scope.doneEditing = function (todo) {
			$scope.editedTodo = null;
			var index = $scope.todos.indexOf(todo);
			$scope.todos[index] = $scope.tempTodo
			$scope.todos[index].title = $scope.tempTodo.title.trim();

			if (!$scope.todos[index].title) {
				$scope.todos.splice(index, 1);
			} else {
				$scope.todos[index].due_date = ($scope.todos[index].due_date)?
					$scope.todos[index].due_date.getStdString() : null;
				$scope.pushTODO($scope.todos[index]);
			}
		};

		$scope.revertEditing = function (todo) {
			return;
			// todos[todos.indexOf(todo)] = $scope.originalTodo;
			// $scope.doneEditing($scope.originalTodo);
		};

		$scope.removeTodo = function (todo) {
			if (window.confirm("sure to delete?")) {
				todoStorage.deleteTODO($scope.targetUser, todo.rowid)
				.success(function(response) {
					if (response.success) {
						$scope.todos.splice($scope.todos.indexOf(todo), 1);
						$scope.currentVersion = response.version;
					}
					else
						$scope.notify("failed to remove todo.");
				}).error(function(error) {
					$scope.notify("failed to remove todo.");
				});
			}
		};

		$scope.clearCompletedTodos = function () {
			$scope.todos = $scope.todos.filter(function (val) {
				return !val.completed;
			});
		};
	});
