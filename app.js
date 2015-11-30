/*
 * @Author: David MacCormick
 * @Date: November 2015
 * 
 * Declares the AngularJS model for quiz application
 * Defines routing for pages in the application
 */


var app = angular.module('app', ['ngRoute', 'ngAnimate']);

app.config(function ($routeProvider) {
        $routeProvider.when('/home', { templateUrl: 'Views/default-view.html' })
                        .when('/quiz', { templateUrl: 'Views/play-view.html' })
                        .when('/create', { templateUrl: 'Views/edit-view.html' })
                        .when('/edit', { templateUrl: 'Views/edit-view.html' })
                        .when('/result', { templateUrl: 'Views/result-view.html' })
                        .when('/message', { templateUrl: 'Views/message-view.html' })
                        .when('/sign-up', { templateUrl: 'Views/sign-up-view.html' })
                        .otherwise({ redirectTo: '/home' });

});


  