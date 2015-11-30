/*
 * @Author: David MacCormick
 * @Date: November 2015
 * 
 * AngularJS controller to control the behaviour of Quiz application
 */

app.controller('QuizController', function ($scope, $location, $window, QuizService, LoginService) {
    
    /* the currently loaded quiz */
    $scope.quiz = {};

    /* the user currently logged in */
    $scope.user = { username: "", password: "" };
    $scope.loginform = { username: "", password: "" };
    $scope.loggedIn = false;

    /* variables for the sign up form */
    $scope.signup = { username:"", password:"", repeatPassword:"", submitted:false, alreadyExistsError:false };

    /* list of quizzes displayed in left panel */
    $scope.quizList = [];

    /* index of the currently selected quiz in the quizList array */
    $scope.quizIndex = -1; 

    /* high scores of the current quiz */
    $scope.highScores = [];

    /* true if the currently loaded quiz is new, false if it already exists in database */
    $scope.isNewQuiz = false;

    /* true if app is in edit mode, false is app is in play mode */
    $scope.isEditMode = false;
    $scope.isQuizOpen = false;
    $scope.isCompleted = false;
    $scope.saved = false;

    $scope.score = 0;

    /* call the following functions on load / page refresh: */
    $scope.onLoad = function () {
        var path = $location.path().replace("/", "");
        
        if (path == "create") {
            $scope.onClickCreate();
        } else if (path == "quiz") {
            $location.path('/home')
        } else if (path == "edit") {
            $location.path('/home');
        }
    };
    

    QuizService.getList().then(
        success = function (response) { $scope.quizList = response.data; },
        fail = function (response) { alert("Failure loading database."); }
    );

    /* when user submits log in credentials */
    $scope.onClickLogin = function () {
        LoginService.login($scope.loginform.username, $scope.loginform.password).success(function (response) {
            if (response == "success") {
                $scope.loggedIn = true;
                $scope.user.username = $scope.loginform.username;
                $scope.loginform.username = "";
                $scope.loginform.password = "";

            } else {
                alert(response);
            }
        });
    };
    $scope.onClickLogout = function () {
        $scope.loggedIn = false;
        $scope.user.username = "";
        $scope.user.password = "";
    };

    $scope.onClickSignUp = function (valid) {
        $scope.signup.submitted = true;
        if (valid && $scope.signup.password == $scope.signup.repeatPassword) {

            LoginService.signup($scope.signup.username, $scope.signup.password).success(function (response) {
                
                if (response == 0) { 
                    $scope.signup.alreadyExistsError = true;
                }
                else if (response == 1) {
                    // success
                    $scope.signup.alreadyExistsError = false;
                    $scope.user.username = $scope.signup.username;
                    $scope.user.password = $scope.signup.password;
                    $scope.loggedIn = true;
                    $location.path('/home');
                }
                else {
                    alert(reponse);
                }
            });
        }
    };

    /* when user clicks on a quiz */
    $scope.onClickQuiz = function (id, name, index) {
        $scope.submitted = false;
        $scope.isQuizOpen = true;
        $scope.isCompleted = false;
        $scope.isNewQuiz = false;
        $scope.saved = false;
        $scope.isEditMode = false;
        $scope.quizIndex = index;
		$scope.highScores = [];
		
        $scope.quiz = QuizService.getQuiz(id).success(function (response) {
            $scope.quiz = new QuizService.Quiz(id, name, response);

            // load the questions into Question objects
            for (var i = 0; i < $scope.quiz.length; i++) {
                var q = $scope.quiz.questions[i];
                q = new QuizService.Question(q.text, q.options, q.correctIndex);
            }
            $location.path('quiz');
        });
    };
    

    /* when user finishes quiz, clicks submit */
    $scope.onClickSubmitQuiz = function (isValid) {
        $scope.submitted = true;
        if (isValid) {
            $scope.isCompleted = true;

            $scope.score = $scope.calculateScore();
        
            $scope.highScores = QuizService.getHighScores($scope.quiz.id, $scope.user.username, $scope.score)
            .success(function (response) {              
                $scope.highScores = response;   
            });
        }
   
    };

    $scope.calculateScore = function () {
        var numCorrect = 0;
        for (var i = 0; i < $scope.quiz.questions.length; i++) {
            if ($scope.quiz.questions[i].selectedAnswer == $scope.quiz.questions[i].correctIndex) {
                numCorrect++;
            } else {           
            }
        }
        return ((numCorrect / $scope.quiz.questions.length) * 100).toFixed(2);
    };

    /* when user clicks on Create button */
    $scope.onClickCreate = function () {
        $scope.pageTitle = "Quiz Generator";
        $location.path('/create');
        $scope.submitted = false;
        $scope.saved = false;
        $scope.isNewQuiz = true;
        $scope.isEditMode = true;
        $scope.quiz = QuizService.createQuiz(); 
    };

    /* user clicks on Edit button */
    $scope.onClickEdit = function () {
        $scope.pageTitle = "Edit Quiz";
        $location.path('/edit');
        $scope.isEditMode = true;
        $scope.submitted = false;
        $scope.saved = false;
    };

    /* user clicks on Save button */
    $scope.onClickSave = function (isValid) {
        $scope.submitted = true;       
        if (isValid) {            
            var id = $scope.quiz.id;
            QuizService.saveQuiz($scope.quiz).success(function (response) {
                // if the quiz is newly created, add it to the left nav
                if (id < 0) {
                    id = response;                    
                    $scope.quizList.push({ name: $scope.quiz.name, id: id });
                    $scope.quizIndex = $scope.quizList.length - 1;
                } else {
                    $scope.quizList[$scope.quizIndex].name = $scope.quiz.name;
                }
                $scope.saved = true;
                $scope.isEditMode = false;
                $scope.submitted = false;
                $scope.message = "Quiz Saved";
                $location.path('/message');
                //$scope.onClickQuiz(id, $scope.quiz.name, $scope.quizIndex);
            }).error(function(response) {
                alert("Error saving quiz: " + response);
            });
        }
    };
    
    $scope.onClickDelete = function () {        
        
        QuizService.deleteQuiz($scope.quiz.id).success(function (response) { 
            $scope.message = "Quiz Deleted ";
            $scope.isEditMode = false;
            $scope.isNewQuiz = false;

            $scope.isQuizOpen = false;
            $location.path('/message');
            
            // check if quiz is listed on the left nav
            if ($scope.quiz.id >= 0) {
                $scope.quizList.splice($scope.quizIndex, 1);
            }
        }).error(function (response) {
            $scope.message = "Failed: " + response;
        });
    };

    $scope.onClickCancel = function () {
        $window.history.back();
    };

    $scope.indexChar = function (index) {
        return String.fromCharCode(97 + index);
    };

    $scope.onLoad();
});
