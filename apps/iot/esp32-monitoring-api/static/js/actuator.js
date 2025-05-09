document.getElementById('ledOn').addEventListener('click', fetchLedOn);
document.getElementById('ledOff').addEventListener('click', fetchLedOff);

document.getElementById('melodyA').addEventListener('click', fetchMelodyA);
document.getElementById('melodyB').addEventListener('click', fetchMelodyB);

const default_ip = '192.168.208.91';

function getIpAddress() {
    return document.getElementById('ipAddress').value.trim();
}

function fetchLedOn() {
    const ipAddress = getIpAddress();
    if (!ipAddress) {
        fetch(`http://${default_ip}/ledon`)
        return;
    }
    fetch(`http://${ipAddress}/ledon`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            document.getElementById('content').innerText = 'LED is on';
        })
        .catch(error => {
            document.getElementById('content').innerText = 'Fetch error: ' + error;
        });
}

function fetchLedOff() {
    const ipAddress = getIpAddress();
    if (!ipAddress) {
        fetch(`http://${default_ip}/ledoff`)
        return;
    }
    fetch(`http://${ipAddress}/ledoff`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            document.getElementById('content').innerText = 'LED is off';
        })
        .catch(error => {
            document.getElementById('content').innerText = 'Fetch error: ' + error;
        });
}

function fetchMelodyA() {
    const ipAddress = getIpAddress();
    if (!ipAddress) {
        fetch(`http://${default_ip}/melodyA`)
        return;
    }
    fetch(`http://${ipAddress}/melodyA`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            document.getElementById('content').innerText = 'Melody A is on';
        })
        .catch(error => {
            document.getElementById('content').innerText = 'Fetch error: ' + error;
        });
}

function fetchMelodyB() {
    const ipAddress = getIpAddress();
    if (!ipAddress) {
        fetch(`http://${default_ip}/melodyB`)
        return;
    }
    fetch(`http://${ipAddress}/melodyB`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            document.getElementById('content').innerText = 'Melody B is on';
        })
        .catch(error => {
            document.getElementById('content').innerText = 'Fetch error: ' + error;
        });
}