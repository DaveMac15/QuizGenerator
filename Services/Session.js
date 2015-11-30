
app.factory('Session', function ($http, $q) {
    var Session = {};

    var defer = $q.defer();
    $http.get('php/session.php').then(function (response) {
        Session.username = response.data.username;
        Session.loggedIn = response.data.loggedIn;
    });
        
   

    


    return Session;
});