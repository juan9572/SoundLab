var audio = document.getElementById('player');
var last_question = ""
var last_answer = ""
var total_questions = 0
var total_options = 4
var volume = volumeElement.value;
var speed = velocityElement.value;
var curr_index = -1
var curr_opt_index = 96

fetch('/moodle/blocks/soundlab/questions_dom.json')
    .then(response => response.json())
    .then(data => { total_questions = data.length - 1 })
    .catch(error => console.error(error));

document.addEventListener('keydown', function (event) {
    if (event.key === "q") { // Tecla q
        audio.pause();
        if (last_question != "") {
            audio.src = last_question;
            audio.volume = volume;
            audio.load();
            audio.play();
        }
    } else if (event.key === "r") { // Tecla r
        audio.pause();
        if (last_answer != "") {
            audio.src = last_answer;
            audio.volume = volume;
            audio.load();
            audio.play();
        }
    } else if (event.key === "h") { // Tecla h
        audio.pause();
        curr_index += 1;
        if (curr_index > total_questions) {
            curr_index = 0;
        }
        audio.src = "/moodle/blocks/soundlab/audio/pregunta_" + curr_index.toString() + "/enunciado.mp3";
        last_question = audio.src;
        audio.volume = volume;
        curr_opt_index = 96;
        audio.load();
        audio.play();
    } else if (event.key === "j") { // Tecla j
        audio.pause();
        curr_index -= 1;
        if (curr_index < 0) {
            curr_index = total_questions;
        }
        audio.src = "/moodle/blocks/soundlab/audio/pregunta_" + curr_index.toString() + "/enunciado.mp3";
        last_question = audio.src;
        audio.volume = volume;
        curr_opt_index = 96;
        audio.load();
        audio.play();
    } else if (event.key === "ArrowUp") { //arrowUp
        audio.pause();
        curr_opt_index -= 1;
        if (curr_opt_index < 97) {
            curr_opt_index = 100;
        }
        audio.src = "/moodle/blocks/soundlab/audio/pregunta_" + curr_index.toString() + "/" + String.fromCharCode(curr_opt_index) + ".mp3";
        last_answer = audio.src;
        audio.volume = volume;
        audio.load();
        audio.play();
    } else if (event.key === "ArrowDown") { //arrowDown
        audio.pause();
        curr_opt_index += 1;
        if (curr_opt_index > 100) {
            curr_opt_index = 97;
        }
        audio.src = "/moodle/blocks/soundlab/audio/pregunta_" + curr_index.toString() + "/" + String.fromCharCode(curr_opt_index) + ".mp3";
        last_answer = audio.src;
        audio.volume = volume;
        audio.load();
        audio.play();
    } else if (event.key === "t") { // Tecla t
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
        let request = `http://api.voicerss.org/?key=e061b559eb42432880064c64462635dc&hl=es-mx&r=${speed}&src=${text}`;
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
    }
});

function play() {
    audio.play();
}

function pause() {
    audio.pause();
}