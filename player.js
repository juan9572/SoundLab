var audio = document.getElementById('player');
var last_question = ""
var last_answer = ""
var current_path = "/moodle/blocks/soundlab/audio/pregunta_0/enunciado.mp3"
var total_questions = 0
var total_options = 4
var curr_index = -1
var curr_opt_index = 96

fetch('/moodle/blocks/soundlab/questions_dom.json')
    .then(response => response.json())
    .then(data => {total_questions = data.length - 1})
.catch(error => console.error(error));


document.addEventListener('keydown', function(event) {
    if (event.key === "q") { // Tecla q
        audio.pause();
        if (last_question != "") {
            audio.src = last_question;
            audio.load();
            audio.play();
        }
    } else if (event.key === "r") { // Tecla r
        audio.pause();
        if (last_answer != "") {
            audio.src = last_answer;
            audio.load();
            audio.play();
        }
    }else if (event.key === "h") {
        audio.pause();
        curr_index += 1;
        if (curr_index > total_questions){
            curr_index = 0;
        }
        audio.src = "/moodle/blocks/soundlab/audio/pregunta_"+ curr_index.toString() +"/enunciado.mp3"
        last_question = audio.src
        audio.load();
        audio.play();
    } else if (event.key === "j") {
        audio.pause();
        curr_index -= 1;
        if (curr_index < 0){
            curr_index = total_questions;
        }
        audio.src = "/moodle/blocks/soundlab/audio/pregunta_"+ curr_index.toString() +"/enunciado.mp3"
        last_question = audio.src
        audio.load();
        audio.play();
    }
    else if (event.key === "ArrowUp") { //arrowUp
        audio.pause();
        curr_opt_index -= 1;
        if (curr_opt_index < 97){
            curr_opt_index = 100;
        }
        audio.src = "/moodle/blocks/soundlab/audio/pregunta_"+ curr_index.toString() +"/" + String.fromCharCode(curr_opt_index) + ".mp3"
        last_answer = audio.src
        audio.load();
        audio.play();
    } else if (event.key === "ArrowDown") { //arrowDown
        audio.pause();
        curr_opt_index += 1;
        if (curr_opt_index > 100){
            curr_opt_index = 97;
        }
        audio.src = "/moodle/blocks/soundlab/audio/pregunta_"+ curr_index.toString() +"/" + String.fromCharCode(curr_opt_index) + ".mp3"
        last_answer = audio.src
        audio.load();
        audio.play();
    }
});

function play() {
  audio.play();
}

function pause() {
  audio.pause();
}
