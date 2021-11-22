@extends('layouts.main', ['title' => 'تنظیمات', 'active' => 'home'])
@section('content')
    <style>
        #wait {
            position: fixed;
            left: 0;
            top: 50px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background-color: rgba(255,255,255,0.8);
        }
        .mode {
            width:90px;
            padding: 0;
        }
    </style>

    <!--
    <div id="wait">
        <div class="alert alert-info text-center">
            <h2>تا بروزرسانی داده، لطفا چند لحظه صبر کنید...</h2>
        </div>
    </div>
    -->

    <div class="row">
        <div>
            <div class="card bg-light">
                <div class="card-header">
                    <h4>کنترلر اصلی</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <button class="mode btn btn-outline-info" id="mode-1" onclick="modeChange('1',true)">دستی</button>
                            <button class="mode btn btn-outline-info" id="mode-0" onclick="modeChange('0',true)">هوشمند</button>
                        </div>
                        <div class="col-md-3">
                            <div class="row">
                                <div class="col-md-6" style="color:#9b59b6;">
                                    <span><i class="fa fa-thermometer-half"></i> &nbsp; دمای هوا</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="badge fs-6 w-75" style="background-color: #9b59b6;" id="tmp"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="row">
                                <div class="col-md-6" style="color:#e67e22;">
                                    <span><i class="fa fa-tint"></i> &nbsp; رطوبت هوا</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="badge fs-6 w-75" style="background-color: #e67e22;" id="hmdt"></div>
                                </div>
                            </div>
                        </div>

                        <!--<div class="col-md-3">
                            <div class="row">
                                <div class="col-md-6 text-success">
                                    <span><i class="fa fa-battery-half"></i> &nbsp; شارژ باطری</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="badge bg-success fs-6 w-50" id="bat"></div>
                                </div>
                            </div>
                        </div>-->

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div style="margin:30px 0;">
            <div class="card">
                <div class="card-header">
                    <h4>وضعیت شیرهای برقی</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                    @foreach($zones as $zone)
                        <div class="col-lg-6" style="padding:20px;">
                            @component('components.valveCard', [
                                'id' => $zone->ID,
                                'status' => $zone->StatusID
                            ])@endcomponent
                            <hr>
                            <div class="row">
                                @foreach($valves[$zone->zoneID] as $valve)
                                    <div class="col-lg-6" style="padding:20px;">
                                        @component('components.valveCardShrinked', [
                                            'id' => $valve->ID,
                                            'status' => $valve->StatusID,
                                            'master' => $zone->ID
                                        ])@endcomponent
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/js/mqttws31.min.js" type="text/javascript"></script>
    <script src="/js/socket.js" type="text/javascript"></script>

    <script>
        var device = "{{$device}}", requests = [], sendTimeout;

        setTopic(`m-iis-${device}`);

        $(document).ready(function() {
            let currentIrrigationModeID = "{{$irrigationModeID}}";
            console.log("currentIrrigationModeID",currentIrrigationModeID);
            modeChange(currentIrrigationModeID);
        });

        function changeValveStatus(id,checked) {
            clearTimeout(sendTimeout);

            $(".loader").show();
            setTimeout(function() {
                $(".loader").fadeOut("slow");
            }, 7000);

            const status = checked ? "1" : "2";
            if(sendMessage(`s-iis-${device}`,`{"func":"PutVCS","main_id":${device},"valve_id":${id},"status":${status},"valve_open_timeout":3600,"irr_turn":0}`)) { // connection is established
                var helpDiv = document.getElementById(`${id}-help`);
                helpDiv.innerText = status === "1" ? "دستور باز کردن شیر ارسال شد. لطفا کمی صبر کنید..." : "دستور بستن شیر ارسال شد. لطفا کمی صبر کنید...";
                helpDiv.classList.remove("alert-info");
                helpDiv.classList.remove("alert-danger");
                helpDiv.classList.add("alert-warning");
                helpDiv.style.visibility = "visible";

                requests[id] = {"status":status,"count":0};
            }
        }

        function parseMessage(msg) {
            var incomingMessage;
            try {
                incomingMessage = JSON.parse(msg);
            } catch(e) {
                console.log(e);
                return;
            }

            switch (incomingMessage.func) {
                case 'PostMCI':
                    if(incomingMessage.main_id == device) {
                        // document.getElementById("wait").style.display = "none";
                        // $("#wait").fadeOut("slow");

                        // battery, temp, humidity
                        let controllerSensors = incomingMessage.sensors.split('&');
                        document.getElementById("tmp").innerText = controllerSensors[1] < -40 ? "نامشخص" :  controllerSensors[1]+"°C";
                        document.getElementById("hmdt").innerText = controllerSensors[2] === "-1" ? "نامشخص" : controllerSensors[2] + "%";
                        // document.getElementById("bat").innerText = controllerSensors[0] + "%";

                        var nodes = incomingMessage.nodes;
                        for(var i=0;i<nodes.length;i++) {
                            var nodeDetails = nodes[i].split("&"),
                                nodeId = nodeDetails[0],
                                nodeStatus = nodeDetails[1],
                                nodeSoilHumidity = nodeDetails[2],
                                nodeBatteryCharge = nodeDetails[3],
                                nodeAlarmCode = nodeDetails[4],
                                nodeTemperature = nodeDetails[5],
                                nodeHumidity = nodeDetails[6],
                                nodeWait = nodeDetails[7],
                                valve = "";

                            switch (nodeStatus) {
                                case "1":
                                    valve = "باز";
                                    break;
                                case "2":
                                    valve = "بسته";
                                    break;
                                case "3":
                                    valve = "باز"; // "در حال باز شدن"; // comment for now
                                    break;
                                case "4":
                                    valve = "بسته"; // "در حال بسته شدن"; // comment for now
                                    break;
                                case "5":
                                    valve = "باز";
                                    break;
                                case "6":
                                    valve = "بسته";
                                    break;
                                case "7":
                                    valve = "باز"; // "در حال باز شدن"; // comment for now
                                    break;
                                case "8":
                                    valve = "بسته"; // "در حال بسته شدن"; // comment for now
                                    break;
                                default:
                                    valve = "نامشخص";
                                    break;
                            }

                            document.getElementById(`${nodeId}-valve`).innerText = valve;
                            document.getElementById(`${nodeId}-soil`).innerText = nodeSoilHumidity < 0 ? "نامشخص" : nodeSoilHumidity + "%";
                            document.getElementById(`${nodeId}-temp`).innerText = nodeTemperature < -40 ? "نامشخص" : nodeTemperature +"°C";
                            document.getElementById(`${nodeId}-humidity`).innerText = nodeHumidity < 0 ? "نامشخص" : nodeHumidity + "%";
                            document.getElementById(`${nodeId}-battery`).innerText = nodeBatteryCharge < 0 ? "نامشخص" : nodeBatteryCharge + "%";

                            if(nodeStatus >= "1" && nodeStatus <= "8") {
                                var helpDiv = document.getElementById(`${nodeId}-help`);

                                // added for now, delete later
                                if(requests[nodeId]) {
                                    if((requests[nodeId].status === 1 && (nodeStatus === 1 || nodeStatus === 3)) ||
                                        (requests[nodeId].status === 2 && (nodeStatus === 2 || nodeStatus === 4))) {
                                        // helpDiv.innerText = "";
                                        helpDiv.style.visibility = "hidden";
                                    }
                                } else {
                                    // helpDiv.innerText = "";
                                    helpDiv.style.visibility = "hidden";
                                }

                                /* comment for now
                                if(["1","2","5","6"].includes(nodeStatus)) {
                                    if(requests[nodeId]) {
                                        if(requests[nodeId].status === nodeStatus) {
                                            helpDiv.innerText = "";
                                            helpDiv.style.visibility = "hidden";
                                        }
                                    } else {
                                        helpDiv.innerText = "";
                                        helpDiv.style.visibility = "hidden";
                                    }
                                } else if(["3","4","7","8"].includes(nodeStatus)) {
                                    // node.prop("disabled", true);

                                    helpDiv.innerText = nodeStatus === "3" ? "شیر در حال باز شدن..." : "شیر در حال بسته شدن...";
                                    helpDiv.classList.remove("alert-warning");
                                    helpDiv.classList.remove("alert-danger");
                                    helpDiv.classList.add("alert-info");
                                    helpDiv.style.visibility = "visible";
                                }*/
                            }

                            var alarmDiv = document.getElementById(`${nodeId}-alarm`);
                            switch (nodeAlarmCode) {
                                case "28":
                                    alarmDiv.innerText = "نیاز به شارژ باطری";
                                    alarmDiv.style.visibility = "visible";
                                    break;
                                case "29":
                                    alarmDiv.innerText = "احتمال قطعی ارتباط با کنترلر";
                                    alarmDiv.style.visibility = "visible";
                                    break;
                                default:
                                    // alarmDiv.innerText = nodeAlarmCode;
                                    alarmDiv.style.visibility = "hidden";
                                    break;
                            }

                            if(requests[nodeId]) {
                                if(nodeWait != requests[nodeId].status) { // send request again
                                    if(requests[nodeId].count < 5) {
                                        requests[nodeId].count++;
                                        sendMessage("s-iis-"+device,`{"func":"PutVCS","main_id":${device},"valve_id":${nodeId},"status":${requests[nodeId].status},"valve_open_timeout":3600,"irr_turn":0}`);
                                    } else {
                                        // clear requests[nodeId]
                                        requests[nodeId] = null;
                                        var helpDiv = document.getElementById(`${nodeId}-help`);
                                        helpDiv.innerText = "خطا در دریافت دستور. لطفا مجددا تلاش کنید.";
                                        helpDiv.classList.remove("alert-info");
                                        helpDiv.classList.remove("alert-warning");
                                        helpDiv.classList.add("alert-danger");
                                        helpDiv.style.visibility = "visible";
                                        // }
                                    }
                                } else {
                                    // clear requests[nodeId]
                                    requests[nodeId] = null;
                                }
                            }
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        function modeChange(newMode, changeInDB=false) {
            let target = $(".btn-valve"), allBtns = $(".mode"), activeBtn = $(`#mode-${newMode}`);

            console.log("newMode",newMode);

            if(newMode === "1") {
                target.prop("disabled", false);
            } else if(newMode === "0") {
                target.prop("disabled", true);
            } else {
                //
            }

            allBtns.removeClass("btn-info");
            allBtns.addClass("btn-outline-info");
            activeBtn.removeClass("btn-outline-info");
            activeBtn.addClass("btn-info");

            if(changeInDB) {
                // close pump
                let pump = device + 991, pid = 1, status = 2;
                sendMessage(`s-iis-${pump}`,`{"func":"PutPCS","main_id":${device},"pump_id":${pid},"status":${status},"irr_turn":0}`);

                // close all valves
                let id = device;
                setInterval(function() {
                    id++;
                    sendMessage(`s-iis-${device}`,`{"func":"PutVCS","main_id":${device},"valve_id":${id},"status":${status},"valve_open_timeout":3600,"irr_turn":0}`);
                }, 5000);

                $.ajax("", {
                    type:"post",

                });
            }
        }
    </script>
@endsection
