/*
 * @Author: David MacCormick
 * @Date: November 2015
 * 
 * AngularJS controller to control the behaviour of Quiz application.
 */

app.controller('QuizController', function ($scope, $location, $window, Session, QuizService, LoginService) {
    $scope.rendering = true;

    /* the currently loaded quiz */
    $scope.quiz = {};

    /* the user currently logged in */
    $scope.loginform = { username: "", password: "" };
    $scope.loggedIn = true;
    $scope.username = "";
    $scope.loginform.submitted = false;
    $scope.loginform.errorMessage = "";

    LoginService.getSessionVariables().then(function (response) {
        $scope.username = response.data.username;
        $scope.loggedIn = response.data.loggedIn;
        $scope.rendering = false;
    }, function (response) {
        $scope.username = "";
        $scope.loggedIn = false;
        $scope.rendering = false;
    });
    
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
        } else if (path == "message") {
            $location.path('/home');
        }
    };
    
    
    QuizService.getList().then(success = function (response) { $scope.quizList = response; });

    /* when user submits log in credentials */
    $scope.onClickLogin = function () {
        $scope.loginform.submitted = true;
        LoginService.login($scope.loginform.username, $scope.loginform.password).success(function (response) {
            
            if (response == 1) {
                $scope.loggedIn = true;
                $scope.username = $scope.loginform.username;
                $scope.loginform.username = "";
                $scope.loginform.password = "";
                $scope.loginform.errorMessage = "";
                $scope.login.submitted = false;
            } else {
                $scope.loginform.errorMessage = response;
            }
        })
		.error(function(response){
			alert(response);
		});
    };

    $scope.onClickLogout = function () {
        
        LoginService.logout().success(function () {
            $scope.loggedIn = false;
            $scope.username = "";
        });
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

                    $scope.loginform.username = $scope.signup.username;
                    $scope.loginform.password = $scope.signup.password;
                    $scope.onClickLogin();
                    $location.path('/home');
                }
                else {
                    alert(reponse);
                }
            })
			.error(function(response){
				alert(response);
			});
        }
    };

    /* when user clicks on a quiz */
    $scope.onClickQuiz = function (id, name, index) {
        $scope.scrollTop();
        $scope.submitted = false;
        $scope.isQuizOpen = true;
        $scope.isCompleted = false;
        $scope.isNewQuiz = false;
        $scope.saved = false;
        $scope.isEditMode = false;
        $scope.quizIndex = index;
		$scope.highScores = [];
		
		//alert("id=" + id + ",index=" + index + ", name=" + name);
		$scope.quiz = QuizService.getQuiz(id)
            .success(function (response) {
                $scope.quiz = new QuizService.Quiz(id, name, response);
				
                // load the questions into Question objects
                for (var i = 0; i < $scope.quiz.length; i++) {
                    var q = $scope.quiz.questions[i];
                    q = new QuizService.Question(q.text, q.options, q.correctIndex);
                }
                $location.path('quiz');
            })
			.error(function(response){
				alert("Error getting quiz from database.");
			});
    };
    
    /* when user finishes quiz, clicks submit */
    $scope.onClickSubmitQuiz = function (isValid) {

        $scope.submitted = true;
        if (isValid) {
            $scope.scrollTop();
            $scope.isCompleted = true;

            $scope.score = $scope.calculateScore();
			
			// if user is logged in, add new highscore to database
			if($scope.loggedIn){
				QuizService.addHighscore($scope.quiz.id, $scope.username, $scope.score)
				.success(function(response) {
					
					// display highscores
					$scope.highScores = QuizService.getHighScores($scope.quiz.id)
					.success(function (response) {              
						$scope.highScores = response;   
					});
				});
			} else {
				// display highscores
				$scope.highScores = QuizService.getHighScores($scope.quiz.id)
				.success(function (response) {              
					$scope.highScores = response;   
				});
			}
		}
   
    };

    /* returns the user's score as a percentage */
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
        $scope.scrollTop();
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
        $scope.scrollTop();
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
            $scope.scrollTop();

            var id = $scope.quiz.id;
			
			// if quiz is newly created, add it to the database, then update the contents
			if(id < 0){
				
				QuizService.addQuiz().success(function(response){
					id = parseInt(response);
					$scope.quiz.id = id;
					// add new quiz to the right nav
					$scope.quizList.push({ name: $scope.quiz.name, id: id });
                    $scope.quizIndex = $scope.quizList.length - 1;
                    $scope.message = "Quiz has been added to the database.";
					
					// save the quiz
					QuizService.saveQuiz($scope.quiz).then(function (response) {
						$scope.quizList[$scope.quizIndex].name = $scope.quiz.name;
						
						$scope.saved = true;
						$scope.isEditMode = false;
						$scope.submitted = false;               
						$location.path('/message');
					});
				});
				
			} else { // save the quiz
				QuizService.saveQuiz($scope.quiz).then(function (response) {
				   

					$scope.quizList[$scope.quizIndex].name = $scope.quiz.name;
					$scope.message = "Changes were saved to the database.";
					
					$scope.saved = true;
					$scope.isEditMode = false;
					$scope.submitted = false;               
					$location.path('/message');
				});
			}
        }
    };
    
    /* when user clicks delete button */
    $scope.onClickDelete = function () {        
        
        QuizService.deleteQuiz($scope.quiz.id).then(function (response) { 
            $scope.message = "Quiz Deleted ";
            $scope.isEditMode = false;
            $scope.isNewQuiz = false;

            $scope.isQuizOpen = false;
            $location.path('/message');
            
            // check if quiz is listed on the left nav
            if ($scope.quiz.id >= 0) {                
                $scope.quizList.splice($scope.quizIndex, 1);
            }
        },function (response) {
            $scope.message = "Failed: " + response;
        });
    };

    /* when user clicks Cancel button */
    $scope.onClickCancel = function () {
        $window.history.back();
    };

    /* returns letter from an index (0 => a, 1 => b, 2 => c, etc)  */
    $scope.indexChar = function (index) {
        return String.fromCharCode(97 + index);
    };

    /* causes browser to scroll to the top of the page */
    $scope.scrollTop = function () {
        $("html, body").animate({ scrollTop: 0  }, "slow");
        return false;
    };

    $scope.onLoad();
});
