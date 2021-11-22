<style>
    .btn-valve {
        width: 100%;
    }
</style>
<div class="card" style="margin-bottom:15px;">
    <div class="card-header text-center">
        <h5>شیر برقی {{$id}}</h5>
        <div id="{{$id}}-help" class="alert" style="font-size:12px;visibility:hidden;padding:0;margin:0;">وضعیت شیر</div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <button class="btn btn-outline-primary btn-valve" onclick="changeValveStatus('{{$id}}',true)">باز کردن شیر</button>
            </div>
            <div class="col-md-6">
                <button class="btn btn-outline-danger btn-valve" onclick="changeValveStatus('{{$id}}',false)">بستن شیر</button>
            </div>
        </div>

        <hr>

        <div class="row text-dark" style="background-color:#81ecec; padding:5px 2px;">
            <div class="col-md-6">
                <span><i class="fa fa-faucet"></i> &nbsp; وضعیت شیر</span>
            </div>
            <div class="col-md-6 text-center">
                <div id="{{$id}}-valve">{{\Illuminate\Support\Facades\DB::table('statuses')->find($status)->StatusName}}</div>
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-md-6 text-info">
                <span><i class="fa fa-water"></i> &nbsp; رطوبت خاک</span>
            </div>
            <div class="col-md-6 text-center">
                <div class="badge bg-info fs-6 w-50" id="{{$id}}-soil"></div>
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-md-6" style="color:#9b59b6;">
                <span><i class="fa fa-thermometer-half"></i> &nbsp; دمای هوا</span>
            </div>
            <div class="col-md-6 text-center">
                <div class="badge fs-6 w-50" style="background-color: #9b59b6;" id="{{$id}}-temp"></div>
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-md-6" style="color:#e67e22;">
                <span><i class="fa fa-tint"></i> &nbsp; رطوبت هوا</span>
            </div>
            <div class="col-md-6 text-center">
                <div class="badge fs-6 w-50" style="background-color: #e67e22;" id="{{$id}}-humidity"></div>
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-md-6 text-success">
                <span><i class="fa fa-battery-half"></i> &nbsp; شارژ باطری</span>
            </div>
            <div class="col-md-6 text-center">
                <div class="badge bg-success fs-6 w-50" id="{{$id}}-battery"></div>
            </div>
        </div>

        <br>

        <div id="{{$id}}-alarm" class="alert alert-danger text-center" role="alert" style="margin:0;padding:0;visibility:hidden;"></div>
    </div>
</div>
