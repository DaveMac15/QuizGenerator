/*
 * @Author: David MacCormick
 * @Date: November 2015
 * 
 * AngularJS service for Quiz application 
 *    -- performs CRUD operations for quizzes in the application
 */

app.service('QuizService', function ($http, $q) {
    var parent = this;

    /* Quiz object contructor */
    this.Quiz = function (id, name, questions) {
        this.id = id;
        this.name = name;
        this.questions = questions;
        this.addQuestion = function () {
            this.questions.push(new parent.Question("", [{ text: "" }, { text: "" }, { text: "" }, {text:""}], 0));
        };
        
        
    };

    /* Question object constructor */
    this.Question = function (text, options, correctIndex) {
        this.text = text;
        this.options = options;
        this.correctIndex = correctIndex;
        this.addOption = function () {
            
            this.options.push({text:""});
        };
        this.selectedAnswer = "-1";
    };

    /* get quiz names for the left panel */
    this.getList = function () {
        return $http.get("quiz_api.php/names")
            .then(function (response) {
                return response.data;
            });
    };

    /* load quiz from database using quiz id */
    this.getQuiz = function (id) {
        return $http.get("quiz_api.php/quiz/" + id);
            
    };

    /* create a new blank quiz */
    this.createQuiz = function(){
        var q = new this.Quiz(-1, "", []);
        q.addQuestion(); // add an initial blank question
        return q;
    };
	
	this.addQuiz = function() {
		return $http.get("quiz_api.php/quiz");
	}
	
    /* save quiz to the database */
    this.saveQuiz = function (quiz) {
        var jsonQuiz = angular.toJson(quiz);
        return $http.put('quiz_api.php/quiz/' + quiz.id, quiz);
    };

    /* delete quiz from the database */
    this.deleteQuiz = function (id) {
        return $http.delete("quiz_api.php/quiz/" + id);
    };

    this.getHighScores = function (id) {
        return $http.get("quiz_api.php/highscores/" + id);
    };
	this.addHighscore = function(id, username, score){
		return $http.post('quiz_api.php/highscores/' + id, {username:username, score:score });
	}

    
});