@extends('layouts.main', ['title' => 'درباره', 'active' => 'configuration'])
@section('content')
    <div class="card">
        <div class="card-header">
            <h4>پیکربندی</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <form method="POST" action="{{ route('site.config.store') }}">
                    @csrf
                    <div class="row">
                        <label for="valve_id" class="col-sm-1 col-form-label" style="text-align:left;">کنترلر</label>
                        <div class="col-sm-2">
                            <select id="valve_id" name="valve_id" class="form-select"
                                style="text-align:left;direction:rtl;" onchange="selectValve(this.value)">
                                <option value="-1">انتخاب کنید</option>
                                @foreach ($valves as $key => $value)
                                    <option value="{{ $key }}">شیر برقی {{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div id="content" style="margin-right: 55px;margin-top:20px">
                            <div class="row">
                            @foreach ($sensors as $sensor)
                                <div class="col-md-4" style="margin:5px 0;">
                                    <input type="checkbox" name="mysensors[{{ $sensor->ID }}]"
                                           id="sensor-{{ $sensor->ID }}">
                                    <span>{{ $sensor->SensorFeatureName }} </span>
                                </div>
                            @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12" style="justify-content: center;display:flex;">
                        <button type="submit" class="btn btn-success">ذخیره تغییرات </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function selectValve(selected) {
            $("input[type=checkbox]").prop("checked", false);
            if (selected >= 0) {
                $.ajax("/valveSensors/" + selected, {
                    type: 'get',
                    success: function(response) {

                        for (let i = 0; i < response.data.length; i++) {
                            $("#sensor-" + response.data[i].SensorFeatureID).prop("checked", true);
                        }
                    }
                });
            }
        }
    </script>
@endsection
