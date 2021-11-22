@extends('layouts.main', ['title' => 'خانه', 'active' => 'settings'])
@section('content')
    <div class="row">
        <div class="col-lg-6" style="margin:30px 0;">
            <div class="card">
                <div class="card-header">
                    <h4>کنتور 1</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <label>حجم آب مصرفی</label>
                        </div>
                        <div class="col-lg-6">
                            <div class="badge bg-info text-dark fs-6">
                                <label id="flow1" class="changing-item">0</label>&nbsp;<label>متر مکعب</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6" style="margin:30px 0;">
            <div class="card">
                <div class="card-header">
                    <h4>کنتور 2</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <label>حجم آب مصرفی</label>
                        </div>
                        <div class="col-lg-6">
                            <div class="badge bg-info text-dark fs-6">
                                <label id="flow2" class="changing-item">0</label>&nbsp;<label>متر مکعب</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mx-auto" style="margin:30px 0;">
            <div class="card">
                <div class="card-header">
                    <h4>وضعیت پمپ</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12" style="text-align:center;">
                            <span> روشن </span>
                            <label class="switch">
                                <input type="checkbox" name="pump_relay" id="pump_relay" onchange="changePumpStatus(this.checked)">
                                <span class="slider round"></span>
                            </label>
                            <span> خاموش </span>
                        </div>

                        <h5 id="pump-response" class="text-danger text-center" style="visibility:hidden;padding-top:15px;"></h5>

                        <div class="alert alert-danger text-center" id="pump-alarm" style="visibility:hidden;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/js/mqttws31.min.js" type="text/javascript"></script>
    <script src="/js/socket.js" type="text/javascript"></script>

    <script>
        var pump = "{{$pump}}", device = "{{$deviceId}}", pid = pump.substr(6,1);
        setTopic(`p-iis-${pump}`);

        function sendWithInterval(status) {
            $("#pump_relay").prop("disabled", true);
            sendMessage(`s-iis-${pump}`,`{"func":"PutPCS","main_id":${device}000,"pump_id":${pid},"status":${status},"irr_turn":0}`);
            let counter = 2;
            let sendInterval = setInterval(function () {
                if(counter > 0)
                    sendMessage(`s-iis-${pump}`,`{"func":"PutPCS","main_id":${device}000,"pump_id":${pid},"status":${status},"irr_turn":0}`);
                else {
                    clearInterval(sendInterval);
                    $("#pump_relay").prop("disabled", false);
                }
                counter--;
            }, 3000);
        }

        function changePumpStatus(checked) {

            $(".loader").show();
            setTimeout(function() {
                $(".loader").fadeOut("slow");
            }, 3000);

            const status = checked ? 1 : 2;
            if(status === 1) {
                // check that all valves are closed
                $.ajax("/checkIfAtLeastOneValveIsOpen/"+device, {
                    type: 'get',
                    success: function (response) {
                        if (response.data.isOpen) {
                            sendWithInterval(status);
                        } else {
                            document.getElementById("pump-response").style.visibility = "visible";
                            document.getElementById("pump-response").innerText = "قبل از باز کردن پمپ، حداقل یک شیر باید باز باشد.";
                            $("#pump_relay").prop("checked", false);
                        }
                    }
                });
            } else {
                sendWithInterval(status);
            }
        }

        function parseMessage(msg) {
            var incomingMessage = JSON.parse(msg);

            switch (incomingMessage.func) {
                case 'PostPCI': // flow data
                    let incomingPump = incomingMessage.main_id.toString().substr(0,4) + "99" + incomingMessage.pump_id;

                    if(incomingPump === pump) {
                        var flows = incomingMessage.flow,
                            relay = incomingMessage.status,
                            alarm = incomingMessage.alarm,
                            flow1Element = document.getElementById("flow1"),
                            flow2Element = document.getElementById("flow2");


                        flow1Element.innerText = flows[0]/100;
                        flow1Element.style.color = "red";
                        flow2Element.innerText = flows[1]/100;
                        flow2Element.style.color = "red";

                        setTimeout(function() {
                            flow1Element.style.color = "#222";
                            flow2Element.style.color = "#222";
                        }, 2000);

                        $("#pump_relay").prop("checked", relay === 1);

                        if(alarm.length>0) {
                            switch (alarm) {
                                case "31":
                                    $("#pump-alarm").css({"visibility":"visible"});
                                    $("#pump-alarm").text("پمپ بيش از اندازه روشن بوده است.");
                                    break;
                                case "32":
                                    $("#pump-alarm").css({"visibility":"visible"});
                                    $("#pump-alarm").text("فشار آب از حد مجاز پاين‌تر است.");
                                    break;
                                default:
                                    $("#pump-alarm").css({"visibility":"hidden"});
                                    break;
                            }

                        } else {
                            $("#pump-alarm").css({"visibility":"hidden"});
                        }
                    }
                    break;
                default:
                    break;
            }
        }
    </script>
@endsection
