/*
 * @Author: David MacCormick
 * @Date: November 2015
 * 
 * AngularJS service for Quiz application 
 *    -- performs CRUD operations for quizzes in the application
 */

app.service('QuizService', function ($http) {
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
        return $http.get("php/quiz-names.php");
    };

    /* load quiz from database using quiz id */
    this.getQuiz = function (id) {
        return $http.get("php/quiz-load.php?id=" + id);
    };

    /* create a new blank quiz */
    this.createQuiz = function(){
        var q = new this.Quiz(-1, "", []);
        q.addQuestion(); // add an initial blank question
        return q;
    };

    /* save quiz to the database */
    this.saveQuiz = function (quiz) {
        var jsonQuiz = angular.toJson(quiz);
        return $http.post('php/save-quiz.php', quiz);
    };

    /* delete quiz from the database */
    this.deleteQuiz = function (id) {
        return $http.post("php/delete-quiz.php", id);
    };

    this.getHighScores = function (quiz_id, username, score) {
        return $http.post("php/get-highscores.php", {quiz_id: quiz_id, username:username, score:score });
    };

    
});