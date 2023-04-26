var audio = document.getElementById('player');

document.addEventListener('keydown', function(event) {
  if (event.keyCode === 13) { // Tecla Enter
    audio.pause();
    audio.src = "/moodle/blocks/soundlab/audio/pregunta_0/total.mp3";
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
