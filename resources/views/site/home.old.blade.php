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
            background-color: rgba(255,255,255,0.5);
        }
    </style>
    <div id="wait">
        <div class="alert alert-info text-center">
            <h2>تا بروزرسانی داده، لطفا چند لحظه صبر کنید...</h2>
        </div>
    </div>

    <div class="row">
        <div>
            <div class="card bg-light">
                <div class="card-header">
                    <h4>کنترلر اصلی</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-6" style="color:#9b59b6;">
                                    <span><i class="fa fa-thermometer-half"></i> &nbsp; دمای هوا</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="badge fs-6 w-50" style="background-color: #9b59b6;" id="tmp"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-6" style="color:#e67e22;">
                                    <span><i class="fa fa-tint"></i> &nbsp; رطوبت هوا</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="badge fs-6 w-50" style="background-color: #e67e22;" id="hmdt"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-6 text-success">
                                    <span><i class="fa fa-battery-half"></i> &nbsp; شارژ باطری</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="badge bg-success fs-6 w-50" id="bat"></div>
                                </div>
                            </div>
                        </div>
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
                        @foreach($valves as $valve)
                            <div class="col-lg-4" style="padding:20px;">
                                @component('components.valveCard', [
                                    'valveTitle' => $valve['title'],
                                    'valveName' => $valve['name'],
                                    'valveChecked' => $valve['checked']
                                ])@endcomponent
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

        function changeValveStatus(id,checked) {
            clearTimeout(sendTimeout);

            $(".loader").show();
            setTimeout(function() {
                $(".loader").fadeOut("slow");
            }, 3000);

            const status = checked ? "1" : "2";
            if(sendMessage(`s-iis-${device}`,`{"func":"order","main_id":"${device}","node":"${id}","status":"${status}"}`)) { // connection is established
                var helpDiv = document.getElementById(`${id}-help`);
                helpDiv.innerText = status === "1" ? "دستور باز کردن شیر ارسال شد. لطفا کمی صبر کنید..." : "دستور بستن شیر ارسال شد. لطفا کمی صبر کنید...";
                helpDiv.classList.remove("alert-info");
                helpDiv.classList.remove("alert-danger");
                helpDiv.classList.add("alert-warning");
                helpDiv.style.visibility = "visible";

                var counter = 0,
                    sendInterval = setInterval(function() {
                        if (counter<3) {
                            sendMessage("s-iis-"+device,`{"func":"order","main_id":"${device}","node":"${id}","status":"${status}"}`);
                            counter++;
                        } else {
                            clearInterval(sendInterval);
                        }
                    }, 5000);

                requests[id] = status;


                sendTimeout = setTimeout(function() {
                    var cnt = 0;
                    var intvl = setInterval(function() {
                        if(cnt<3) {
                            var currentStatus = $(`#${id}`).prop("checked") ? "1" : "2";
                            if(requests[id] !== currentStatus) {
                                sendMessage("s-iis-"+device,`{"func":"order","main_id":"${device}","node":"${id}","status":"${status}"}`);
                                var secondCounter = 0,
                                    secondSendInterval = setInterval(function() {
                                        if (secondCounter<3) {
                                            sendMessage("s-iis-"+device,`{"func":"order","main_id":"${device}","node":"${id}","status":"${status}"}`);
                                            secondCounter++;
                                        } else {
                                            clearInterval(secondSendInterval);
                                            helpDiv.innerText = "دستور روی شیر اعمال نشد. این احتمال وجود دارد که ارتباط شیر برقی با کنترل اصلی قطع شده باشد. لطفا چند دقیقه دیگر مجددا تلاش کنید.";
                                            helpDiv.classList.remove("alert-info");
                                            helpDiv.classList.remove("alert-warning");
                                            helpDiv.classList.add("alert-danger");
                                            helpDiv.style.visibility = "visible";
                                        }
                                    }, 5000);
                            } else {
                                clearInterval(intvl);
                            }
                            cnt++;
                        } else {
                            clearInterval(intvl);
                        }
                    }, 60000);
                }, 60000); // 1 minute
            }
        }

        function removeAllWhitespace(input) {
            return input.replace(/ /g,'');
        }

        function parseMessage(msg) {
            var incomingMessage = JSON.parse(msg);

            switch (incomingMessage.func) {
                case 'PostParams':
                    if(incomingMessage.main_id === device) {
                        // document.getElementById("wait").style.display = "none";

                        $("#wait").fadeOut("slow");

                        document.getElementById("tmp").innerText = incomingMessage.tmp +"°C";
                        document.getElementById("hmdt").innerText = incomingMessage.hmdt + "%";
                        document.getElementById("bat").innerText = incomingMessage.bat + "%";

                        var nodes = incomingMessage.nodes;
                        for(var i=0;i<nodes.length;i++) {
                            var nodeDetails = nodes[i].split("&"),
                                nodeId = nodeDetails[0].length === 1 ? "0"+nodeDetails[0] : nodeDetails[0],
                                nodeStatus = nodeDetails[1],
                                nodeSoilHumidity = nodeDetails[2],
                                nodeBatteryCharge = nodeDetails[3],
                                nodeAlarmCode = nodeDetails[4],
                                nodeTemperature = nodeDetails[5],
                                nodeHumidity = nodeDetails[6],
                                valve = "";

                            switch (nodeStatus) {
                                case "1":
                                    valve = "باز";
                                    break;
                                case "2":
                                    valve = "بسته";
                                    break;
                                case "3":
                                    valve = "در حال باز شدن";
                                    break;
                                case "4":
                                    valve = "در حال بسته شدن";
                                    break;
                                default:
                                    valve = "نامشخص";
                                    break;
                            }

                            document.getElementById(`${nodeId}-valve`).innerText = valve;
                            document.getElementById(`${nodeId}-soil`).innerText = nodeSoilHumidity + "%";
                            document.getElementById(`${nodeId}-temp`).innerText = nodeTemperature +"°C";
                            document.getElementById(`${nodeId}-humidity`).innerText = nodeHumidity + "%";
                            document.getElementById(`${nodeId}-battery`).innerText = nodeBatteryCharge + "%";

                            if(nodeStatus >= "1" && nodeStatus <= "4") {
                                var helpDiv = document.getElementById(`${nodeId}-help`);
                                if(nodeStatus === "1" || nodeStatus === "2") {
                                    if(requests[nodeId] && requests[nodeId] === nodeStatus) {
                                        helpDiv.innerText = "";
                                        helpDiv.style.visibility = "hidden";
                                    }
                                } else if(nodeStatus === "3" || nodeStatus === "4") {
                                    // node.prop("disabled", true);
                                    helpDiv.innerText = nodeStatus === "3" ? "شیر در حال باز شدن..." : "شیر در حال بسته شدن...";
                                    helpDiv.classList.remove("alert-warning");
                                    helpDiv.classList.remove("alert-danger");
                                    helpDiv.classList.add("alert-info");
                                    helpDiv.style.visibility = "visible";
                                }
                            }

                            var alarmDiv = document.getElementById(`${nodeId}-alarm`);
                            switch (nodeAlarmCode) {
                                case "10":
                                    alarmDiv.innerText = "نیاز به شارژ باطری";
                                    alarmDiv.style.visibility = "visible";
                                    break;
                                case "5":
                                    alarmDiv.innerText = "احتمال قطعی ارتباط با کنترلر";
                                    alarmDiv.style.visibility = "visible";
                                    break;
                                default:
                                    // alarmDiv.innerText = nodeAlarmCode;
                                    alarmDiv.style.visibility = "hidden";
                                    break;
                            }
                        }
                    }
                    break;
                default:
                    break;
            }
        }
    </script>
@endsection
