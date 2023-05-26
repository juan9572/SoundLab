window.is_essay = false;
var audio = document.getElementById('player');
var quiz_id = document.currentScript.getAttribute('data-quiz_id');
var questions = JSON.parse(document.currentScript.getAttribute('data-questions'));
var last_question = "";
var last_answer = "";
var total_questions = 0;
var volume = volumeElement.value;
var speed = velocityElement.value;
var curr_index = -1;
var curr_opt_index = 96;
var last_word = ""; //Realizar un script que se encargue de finalizarlo.

window.addEventListener("load", (event) => {
    get_ids_questions();
    let first = set_answered_already();
    center_attention_in_question(first);
    curr_index = first - 1;
    total_questions = questions.length - 1;
    audio.pause();
    audio.src = "/moodle/blocks/soundlab/audio/" + quiz_id + "/start" +
        (speed == -5 ? "_s" : speed == 0 ? "_n" : "_f") + ".mp3";
    audio.volume = volume;
    audio.load();
    audio.play();
});

document.addEventListener('keydown', function (event) {
    if (event.key === "q" && window.is_essay === false) { // Tecla q
        audio.pause();
        if (last_question != "") {
            center_attention_in_question(curr_index);
            audio.src = last_question;
            audio.volume = volume;
            audio.load();
            audio.play();
        }
    } else if (event.key === "r" && window.is_essay === false) { // Tecla r
        audio.pause();
        if (last_answer != "") {
            center_attention_in_answer(curr_index, opt_index);
            audio.src = last_answer;
            audio.volume = volume;
            audio.load();
            audio.play();
        }
    } else if (event.key === "h" && window.is_essay === false) { // Tecla h
        audio.pause();
        if(curr_index >= 0 && last_answer != "")
            decenter_attention_in_answer(curr_index, curr_opt_index - 97);
        curr_index += 1;
        if (curr_index > total_questions) {
            curr_index = 0;
        }
        center_attention_in_question(curr_index);
        audio.src = "/moodle/blocks/soundlab/audio/" + quiz_id + "/pregunta_" +
            curr_index.toString() + "/enunciado" +
            (speed == -5 ? "_s" : speed == 0 ? "_n" : "_f") + ".mp3";
        last_question = audio.src;
        audio.volume = volume;
        curr_opt_index = 96;
        last_answer = "";
        audio.load();
        audio.play();
    } else if (event.key === "j" && window.is_essay === false) { // Tecla j
        audio.pause();
        if(curr_index > 0 && last_answer != "")
            decenter_attention_in_answer(curr_index, curr_opt_index - 97);
        curr_index -= 1;
        if (curr_index < 0) {
            curr_index = total_questions;
        }
        center_attention_in_question(curr_index);
        audio.src = "/moodle/blocks/soundlab/audio/" + quiz_id + "/pregunta_" +
            curr_index.toString() + "/enunciado" +
            (speed == -5 ? "_s" : speed == 0 ? "_n" : "_f") + ".mp3";
        last_question = audio.src;
        audio.volume = volume;
        curr_opt_index = 96;
        last_answer = "";
        audio.load();
        audio.play();
    } else if (event.key === "ArrowUp" && window.is_essay === false) { // Tecla arrowUp
        audio.pause();
        curr_opt_index -= 1;
        if (curr_opt_index < 97) {
            curr_opt_index = 97 + questions[curr_index]["answers"].length - 1;
        }
        center_attention_in_answer(curr_index ,curr_opt_index - 97);
        audio.src = "/moodle/blocks/soundlab/audio/" + quiz_id + "/pregunta_" +
            curr_index.toString() + "/" + String.fromCharCode(curr_opt_index - 32) +
            (speed == -5 ? "_s" : speed == 0 ? "_n" : "_f") + ".mp3";
        last_answer = audio.src;
        audio.volume = volume;
        audio.load();
        audio.play();
    } else if (event.key === "ArrowDown" && window.is_essay === false) { // Tecla arrowDown
        audio.pause();
        curr_opt_index += 1;
        if (curr_opt_index - 97 > questions[curr_index]["answers"].length - 1) {
            curr_opt_index = 97;
        }
        center_attention_in_answer(curr_index ,curr_opt_index - 97);
        audio.src = "/moodle/blocks/soundlab/audio/" + quiz_id + "/pregunta_" +
            curr_index.toString() + "/" + String.fromCharCode(curr_opt_index - 32) +
            (speed == -5 ? "_s" : speed == 0 ? "_n" : "_f") + ".mp3";
        last_answer = audio.src;
        audio.volume = volume;
        audio.load();
        audio.play();
    } else if (event.key == "F2" && window.is_essay === false) { // Tecla F2
        audio.pause();
        audio.src = "/moodle/blocks/soundlab/audio/" + quiz_id + "/help" +
            (speed == -5 ? "_s" : speed == 0 ? "_n" : "_f") + ".mp3";
        audio.volume = volume;
        audio.load();
        audio.play();
    } else if (event.key === "f" && window.is_essay === false) { // Tecla f
        document.getElementById("mod_quiz-next-nav").click();
    } else if (event.key === "t" && window.is_essay === false) { // Tecla t
        audio.pause();
        let timer = document.getElementById("quiz-time-left").innerText.split(":");
        let hours = parseInt(timer[0]);
        let minutes = parseInt(timer[1]);
        let seconds = parseInt(timer[2]);
        let text = "Faltan, " + (
            hours > 1 ? hours + " horas" :
                hours != 0 ? "una hora" : ""
        ) + (hours > 0 ? " con " : "") + (
                minutes > 1 ? minutes + " minutos" :
                    minutes != 0 ? "un minuto" : ""
            ) + (seconds > 0 && minutes > 0 ? " y " : "") + (
                seconds > 1 ? seconds + " segundos" :
                    seconds != 0 ? "un segundo" : ""
            ) + ", para finalizar el cuestionario.";
        let request = `http://api.voicerss.org/?key=8a35f597a70b42a8a86ba737d2a1ee2a&hl=es-mx&r=${speed}&c=MP3&f=16khz_16bit_stereo&v=Silvia&src=${text}`;
        // hace una petición HTTP a la API de VoiceRSS
        fetch(request)
            .then(response => {
                // verifica que la respuesta sea válida
                if (!response.ok) {
                    throw new Error('Error al obtener la respuesta de la API');
                }
                // extrae el contenido de la respuesta
                return response.blob();
            })
            .then(blob => {
                // crea una URL temporal para el archivo de audio
                const url = URL.createObjectURL(blob);
                // asigna la URL al elemento de audio
                audio.src = url;
                audio.volume = volume;
                audio.load();
                audio.play();
            })
            .catch(error => {
                console.error(error);
            });
    } else if (event.key === "i" && window.is_essay === false) { // Tecla i
        if (last_question != "") {
            type = "";
            if(questions[curr_index].type === "multichoice") {
                type = "mlt";
            }
            else if(questions[curr_index].type === "truefalse") {
                type = "tf";
            }
            else if(questions[curr_index].type === "essay") {
                type = "opn";
            }
            audio.pause();
            audio.src = "/moodle/blocks/soundlab/audio/" + quiz_id + "/type" + type +
                (speed == -5 ? "_s" : speed == 0 ? "_n" : "_f") + ".mp3";
            audio.volume = volume;
            audio.load();
            audio.play();
        }
    } else if (event.key === "s" && window.is_essay === false) { // Tecla s
        if (last_question != "") {
            ans = "/ans"
            if(!questions[curr_index].answered) {
                ans = "/notAns"
            }
            audio.pause();
            audio.src = "/moodle/blocks/soundlab/audio/" + quiz_id + ans +
                (speed == -5 ? "_s" : speed == 0 ? "_n" : "_f") + ".mp3";
            audio.volume = volume;
            audio.load();
            audio.play();
        }
    } else if (event.key === "Escape" && window.is_essay === true) {
        decenter_attention_in_answer(curr_index ,0);
        window.is_essay = false;
        if(questions[curr_index].answers_ids[0].value != "") {
            questions[curr_index].answered = true;
        }else {
            questions[curr_index].answered = false;
        }
    } else if (event.key === " " && window.is_essay === true) {
        let request = `http://api.voicerss.org/?key=8a35f597a70b42a8a86ba737d2a1ee2a&hl=es-mx&r=${speed}&c=MP3&f=16khz_16bit_stereo&v=Silvia&src=${last_word}`;
        // hace una petición HTTP a la API de VoiceRSS
        fetch(request)
            .then(response => {
                // verifica que la respuesta sea válida
                if (!response.ok) {
                    throw new Error('Error al obtener la respuesta de la API');
                }
                // extrae el contenido de la respuesta
                return response.blob();
            })
            .then(blob => {
                // crea una URL temporal para el archivo de audio
                const url = URL.createObjectURL(blob);
                // asigna la URL al elemento de audio
                audio.src = url;
                audio.volume = volume;
                audio.load();
                audio.play();
            })
            .catch(error => {
                console.error(error);
            });
        last_word = "";
    } else if (event.key === "Enter" && questions[curr_index]["type"] === "essay") {
        window.is_essay = true
    } else if (window.is_essay === true) {
        center_attention_in_answer(curr_index ,0);
        if (/^[a-zA-ZñÑ]$/.test(event.key)) {
            last_word = last_word + event.key;
            audio.pause();
            audio.src = `/moodle/blocks/soundlab/alphabet/${event.key.toUpperCase()}.mp3`;
            audio.volume = volume;
            audio.load();
            audio.play();
        } else if (event.key === "Backspace") {
            last_word = last_word.slice(0, -1);
        }
    }
});

function decenter_attention_in_answer(index, opt_index) {
    questions[index].answers_ids[opt_index].blur();
}

function center_attention_in_answer(index, opt_index) {
    questions[index].answers_ids[opt_index].focus();
}

function center_attention_in_question(index) {
    document.getElementById(questions[index].question_id).scrollIntoView({behavior: "smooth"});
}

function set_html_ids(index, id, answers_ids) {
    questions[index].question_id = id;
    if(questions[index].type == "match") {
        questions[index].answers_ids = answers_ids.querySelectorAll('select');
    }else if(questions[index].type == "essay") {
        questions[index].answers_ids = answers_ids.querySelectorAll('textarea');
    }else {
        const inputs = answers_ids.querySelectorAll('input[type="checkbox"], input[type="radio"]');
        inputs.forEach(input => {
            let isKeyboardEvent = false;
            input.addEventListener('keydown', function(event) {
                if (event.key === "Enter" && window.is_essay === false) {
                    isKeyboardEvent = true;
                    if(questions[index].answered && input.checked) {
                        document.getElementById(
                            questions[index].question_id
                        ).querySelectorAll("a")[2].click();
                        input.checked = false;
                        questions[curr_index].answered = false;
                    } else {
                        input.click();
                        event.preventDefault();
                        modifiedAnswered(curr_index);
                    }
                }
            });
            input.addEventListener('click', function(event) {
                if(isKeyboardEvent) { // Si se dio enter;
                    isKeyboardEvent = false;
                }else if(event.detail === 0) { //Si el evento lo genero el teclado
                    event.preventDefault();
                    return;
                }
            });
        });
        questions[index].answers_ids = inputs;
    }
}

function get_ids_questions() {
    const regex = /question-(\d+)-\d+/;
    const elements = Array.from(document.querySelectorAll("[id^='question-']")).filter(element => regex.test(element.id));
    let index = 0;
    elements.forEach(element => {
        set_html_ids(index, element.id, element.querySelector('.answer'));
        index++;
    });
}

function set_answered_already() {
    for(let i = 0; i < questions.length; i++) {
        if(questions[i].type === "essay") {
            if(questions[i].answers_ids[0].value.trim() != "") {
                questions[i].answered = true;
            }else {
                questions[i].answered = false;
            }
        }else{
            for(let j = 0; j < questions[i].answers.length; j++) {
                if(questions[i].answers_ids[j].checked) {
                    questions[i].answered = true;
                    break;
                }else {
                    questions[i].answered = false;
                }
            }
        }
    }
    let fist_instance = 0;
    for(let i = 0; i < questions.length; i++) {
        if(!questions[i].answered) {
            fist_instance = i;
            break;
        }
    }
    return fist_instance;
}

function modifiedAnswered(index_question) {
    for(let j = 0; j < questions[index_question].answers.length; j++) {
        if(questions[index_question].answers_ids[j].checked) {
            questions[index_question].answered = true;
            break;
        }else {
            questions[index_question].answered = false;
        }
    }
}

function play() {
    audio.play();
}

function pause() {
    audio.pause();
}
