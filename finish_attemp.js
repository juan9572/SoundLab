document.addEventListener("DOMContentLoaded", (event) => {
    var res = false;
    var tbody = document.querySelector('table tbody');
    var count = 0;
    for (var i = 1; i < tbody.rows.length; i++) {
        if(tbody.rows[i].className.includes("notyetanswered")) {
            count++;
        }
    }
    var button = document.getElementById("frm-finishattempt").querySelector("button");
    var returnButton = document.querySelector(".controls .btn-secondary");
    var confirmationButton;
    var cancelButton;
    var audio = document.getElementById("player");
    document.addEventListener("keydown", function (event) {
        if (event.key === "f") {
            res = true;
            button.click();
            word = "Aún faltan " + count + " preguntas sin responder, seguro que quieres terminar el cuestionario.";
            let request = `http://api.voicerss.org/?key=8a35f597a70b42a8a86ba737d2a1ee2a&hl=es-mx&r=0&c=MP3&f=16khz_16bit_stereo&v=Silvia&src=${word}`;
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
                    audio.load();
                    audio.play();
                })
                .catch(error => {
                    console.error(error);
                });
        } else if (event.key === "y" && res) {
            confirmationButton = document.querySelector('.modal-footer .btn-primary');
            confirmationButton.click();
        } else if(event.key === "n" && res) {
            cancelButton = document.querySelector('.modal-footer .btn-secondary');
            cancelButton.click();
            returnButton.click();
        }
    });
});
