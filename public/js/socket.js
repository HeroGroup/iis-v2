var name = Date.now(),
    token="7nlbcTUtpbZP7bnEHvuwfiVBQ8AJ4xK1KU2MBM5QLSuJTHdq0vK6DwxjuVQogpdP",
    client = new Paho.MQTT.Client("mqtt.flespi.io", Number(80), name),
    tpc = "";

client.onConnectionLost = onConnectionLost;
client.onMessageArrived = onMessageArrived;

function setTopic(input) {
    tpc = input;
    attemptConnect();
}

function attemptConnect() {
    client.connect({
        timeout: 1200,
        userName:token,
        password:token,
        useSSL: false,
        keepAliveInterval: 86400, // for one day
        onSuccess:onConnect
    });
}

function onConnect() {
    console.log("onConnect");
    if(tpc.length > 0) {
        client.subscribe(tpc);
    }
}

function onConnectionLost(responseObject) {
    if (responseObject.errorCode !== 0) {
        console.log("onConnectionLost: " + responseObject.errorMessage);
    }

    attemptConnect();
}

function onMessageArrived(message) {
    parseMessage(removeAllWhitespace(message.payloadString));
}

function sendMessage(topic, message) {
    try {
        client.send(topic, message);
        return true;
    } catch (e) {
        alert("شما آفلاین هستید. ابتدا از اتصال دستگاه خود به اینترنت اطمینان پیدا کنید. سپس صفحه را refresh نمایید.");
        return false;
    }
}

function removeAllWhitespace(input) {
    return input.replace(/ /g,'');
}
