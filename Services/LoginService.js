/*
 * @Author: David MacCormick
 * @Date: November 2015
 * 
 * AngularJS login service for Quiz application 
 *    -- performs login and sign up functions
 */
app.service('LoginService', function ($http) {
    this.login = function (name, password) {
        return $http.post("php/login.php", { username: name, password: password });
    };

    this.signup = function (name, password) {
        return $http.post("php/signup.php", { username: name, password: password });
    };

    this.getSessionVariables = function () {
        return $http.get('php/session.php');
    };

    this.logout = function () {
        return $http.get('php/logout.php');
    };


});