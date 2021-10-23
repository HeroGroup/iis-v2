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
                            <label id="flow1">0</label>&nbsp;<label>لیتر</label>
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
                            <label id="flow2">0</label>&nbsp;<label>لیتر</label>
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/js/mqttws31.min.js" type="text/javascript"></script>
    <script src="/js/socket.js" type="text/javascript"></script>

    <script>
        var pump = "{{$pump}}";
        setTopic(`D${pump}`);

        function changePumpStatus(checked) {
            $(".loader").show();
            setTimeout(function() {
                $(".loader").fadeOut("slow");
            }, 5000);

            const status = checked ? "1" : "0";

            var counter = 4;
            var sendInterval = setInterval(function() {
                if (counter>0) {
                    sendMessage(`S${pump}`,`{"func":"relayChange","sender":"server","pump_id":"${pump}","relayStatus":"${status}"}`);
                } else {
                    clearInterval(sendInterval);
                }
            }, 5000);

        }

        function parseMessage(msg) {
            var incomingMessage = JSON.parse(msg);

            switch (incomingMessage.func) {
                case 'IISFD': // flow data
                    if(incomingMessage.Pump_id === pump) {
                        var flows = incomingMessage.Flow,
                            relay = incomingMessage.RelayStatus;

                        document.getElementById("flow1").innerText = flows[0];
                        document.getElementById("flow2").innerText = flows[1];

                        $("#pump_relay").prop("checked", relay === 1);
                    }
                    break;
                default:
                    break;
            }
        }
    </script>
@endsection
