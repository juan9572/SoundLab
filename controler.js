var switchElement = document.querySelector('.switch input[type="checkbox"]');
var volumeElement = document.getElementById('volume');
var velocityElement = document.getElementById('velocity');
let value_content;


document.addEventListener('keydown', function (event) {
    if (event.key === "a" && (window.is_essay === false || !(typeof window.is_essay !== 'undefined' && window.is_essay !== null))) { // Tecla a
        switchElement.checked = !switchElement.checked;
        updateConfigActive();
    } else if(event.altKey && event.key === '1') { //Volume Up
        if(parseFloat(volumeElement.value) + 0.1 <= 1) {
            volumeElement.value = parseFloat(volumeElement.value) + 0.1;
            document.querySelector('#volume-value').textContent = volumeElement.value;
        }
    } else if(event.altKey && event.key === '2') { //Volume Down
        if(parseFloat(volumeElement.value) - 0.1 >= 0) {
            volumeElement.value = parseFloat(volumeElement.value) - 0.1;
            document.querySelector('#volume-value').textContent = volumeElement.value;
        }
    } else if(event.altKey && event.key === '3') { //Speed up
        if(parseInt(velocityElement.value, 10) < 5){
            velocityElement.value = parseInt(velocityElement.value, 10) + 5;
            document.querySelector('#speed-value').textContent = velocityElement.value;
        }
    } else if(event.altKey && event.key === '4') { //Speed down
        if(parseInt(velocityElement.value, 10) > -5){
            velocityElement.value = parseInt(velocityElement.value, 10) - 5;
            document.querySelector('#speed-value').textContent = velocityElement.value;
        }
    }
});

document.addEventListener('keyup', function (event) {
    if(event.altKey && event.key === '1') { //Volume Up
        updateConfigVolume();
    }else if(event.altKey && event.key === '2') { //Volume Down
        updateConfigVolume();
    }else if(event.altKey && event.key === '3') { //Speed up
        updateConfigSpeed();
    }else if(event.altKey && event.key === '4') { //Speed down
        updateConfigSpeed();
    }
});

function updateConfigVolume() {
    const volume = parseFloat(volumeElement.value);
    document.querySelector('#volume-value').textContent = volume;
    value_content = Math.round((1 - volume) * 100);
    updateConfig('SoundLabVolumen', value_content)
}

function updateConfigSpeed() {
    const velocity = parseInt(velocityElement.value, 10);
    document.querySelector('#speed-value').textContent = velocity;
    value_content = velocity === -5 ? 0 : velocity === 0 ? 1 : 2;
    updateConfig('SoundLabVelocidad', value_content)
}

function updateConfigActive() {
    const active = switchElement.checked;
    if (active == true) {
        value_content = "0";
    } else {
        value_content = "1";
    }
    updateConfig('SoundLabActive', value_content)
}

function updateConfig(key, value) {
    new Promise((resolve, reject) => {
        fetch('/moodle/blocks/soundlab/update_config.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ key: key, value: value })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al guardar las preguntas');
                }
                resolve();
            })
            .catch(error => {
                console.error(error);
                reject(error);
            })
    });
    setTimeout(() => {
        location.reload();
    }, 500);
}

volumeElement.addEventListener('change', updateConfigVolume);
velocityElement.addEventListener('change', updateConfigSpeed);
switchElement.addEventListener('click', updateConfigActive);
