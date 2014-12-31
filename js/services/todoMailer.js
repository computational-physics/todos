/*global angular */

angular.module('todomvc')
    .factory('todoMailer', ['$http', function($http) {

    var urlBase = 'mailer.php';
    var todoMailer = {};

    todoMailer.send = function (todo, user) {
        data = {'todo': JSON.stringify(todo), 'user': user};
        return $http({
        	method: "POST",
        	url: urlBase, 
        	data: data
        });
    };

    return todoMailer;
}]);