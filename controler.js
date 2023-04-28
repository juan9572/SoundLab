var switchElement = document.querySelector('.switch input[type="checkbox"]');
var volumeElement = document.getElementById('volume');
var velocityElement = document.getElementById('velocity');
let value_content;

function updateConfigVolume() {
    const volume = parseFloat(volumeElement.value);
    document.querySelector('#volume-value').textContent = volume;
    value_content = Math.round((1 - volume) * 100);
    updateConfig('SoundLabVolumen', value_content)
}

function updateConfigSpeed() {
    const velocity = parseInt(velocityElement.value, 10);
    document.querySelector('#speed-value').textContent = velocity;
    value_content = velocity + 10;
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
