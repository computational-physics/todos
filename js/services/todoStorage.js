/*global angular */

angular.module('todomvc')
    .factory('todoStorage', ['$http', function($http) {

    var urlBase = 'service.php';
    var todoStorage = {};

    todoStorage.checkUpdate = function(user, version) {
        data = {
            'q': 'check',
            't_user': user,
            'version': version
        };
        return $http({
            method: "POST",
            url: urlBase,
            data: data
        });
    };

    todoStorage.getTODOS = function (user) {
    	data = {
            'q': 'get',
            't_user': user
        };
        return $http({
        	method: "POST",
        	url: urlBase, 
        	data: data
        });
    };

    todoStorage.getArchivedTODOS = function (user) {
        data = {
            'q': 'get',
            't_user': user, 
            'archived': 1
        };
        return $http({
            method: "POST",
            url: urlBase, 
            data: data
        });
    };

    todoStorage.addTODO = function (user, todo) {
        data = {
            'q': 'add',
            't_user': user,
            'todo': todo
        };
        return $http({
            method: "POST",
            url: urlBase, 
            data: data
        });
    }

    todoStorage.deleteTODO = function (user, rowid) {
        data = {
            'q': 'delete',
            't_user': user,
            'todo': rowid
        };
        return $http({
            method: "POST",
            url: urlBase, 
            data: data
        });
    }

    todoStorage.updateTODO = function (user, todo) {
    	data = {
            'q': 'update',
            't_user': user,
            'todo': todo
        };
        return $http({
        	method: "POST",
        	url: urlBase, 
        	data: data
        });
    };

    return todoStorage;
}]);