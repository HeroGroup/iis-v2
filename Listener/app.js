var http = require('http');
var mqtt = require('mqtt');
const fs = require('fs');
const axios = require('axios').default;

let token = "7nlbcTUtpbZP7bnEHvuwfiVBQ8AJ4xK1KU2MBM5QLSuJTHdq0vK6DwxjuVQogpdP",
	uploadRoute = "http://ws2.itmc.ir/api/storeDeviceSensorData",
	d = new Date(),
	todayDate = (d.getFullYear().toString())+"-"+((d.getMonth()+1).toString())+"-"+(d.getDate().toString());

const uploadData = async (topic, data) => {
	let body = {"deviceId": topic, "body": data};
    axios.post(uploadRoute, body)
        .then(function (response) {
            // console.log(response.data);
        })
        .catch(function (error) {
            fs.appendFile(`\logs\\${todayDate}.txt`, error.toString() + "\n", function(err) { });
        });
};

const currentTime = () => {
	var today = new Date(),
        hours = today.getHours(),
        minutes = today.getMinutes(),
        seconds = today.getSeconds(),
        time = (hours.toString().length<2 ? '0'+hours : hours) + ":" + (minutes.toString().length < 2 ? '0'+minutes : minutes) + ":" + (seconds.toString().length < 2 ? '0'+seconds : seconds);

	return time;
}

client = mqtt.connect('ws://mqtt.flespi.io', { username:token , password:token });

client.on('connect', function () {
	let topic = "p-iis-1001991";
	client.subscribe(topic, function(err) {
		let pumpDevice = topic.substr(6);
		if(err) {
			fs.appendFile(`\logs\\${todayDate}\\pump\\${pumpDevice}.txt`, currentTime() + " " + err.toString() + "\r\n", function(err) { });
		} else {
			fs.appendFile(`\logs\\${todayDate}\\pump\\${pumpDevice}.txt`, `${currentTime()} subscribed to ${topic} \r\n`, function(err) { });
		}
	});

});

client.on('message', function (topic, message) {
    try {
		topic = topic.substr(6); // remove p-iis-
        var incomingJson = JSON.parse(message.toString());
        switch (incomingJson.funcName) {
            case "PostPCI":

                break;
            default:
                break;
        }

        fs.appendFile(`\logs\\${todayDate}\\pump\\${topic}.txt`, currentTime() + " => " + message.toString() + "\r\n", function(err) { });
    } catch (e) {
        fs.appendFile(`\logs\\${todayDate}\\pump\\${topic}.txt`, err.toString() + "\r\n", function(err) { });
    }
});
