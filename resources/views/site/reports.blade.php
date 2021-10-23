@extends('layouts.main', ['title' => 'گزارشات', 'active' => 'reports'])
@section('content')
    <div class="card">
        <div class="card-header">
            <h4>گزارشات</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <label for="reportType" class="col-sm-1 col-form-label" style="text-align:left;">گزارش</label>
                <div class="col-sm-2">
                    <select id="reportType" class="form-control">
                        <option value="1" selected>گزارش میزان آبیاری</option>
                        <option value="2">گزارش میانگین روزانه رطوبت خاک</option>
                        <option value="3">گزارش میانگین روزانه رطوبت هوا</option>
                        <option value="4">گزارش میانگین روزانه دمای هوا</option>
                        <option value="5">گزارش پارامترهای هواشناسی</option>
                    </select>
                </div>

                <label for="year" class="col-sm-1 col-form-label" style="text-align:left;">سال</label>
                <div class="col-sm-2">
                    <input type="text" name="year" id="year" placeholder="برای مثال 1400" class="form-control" required />
                </div>

                <label for="month" class="col-sm-1 col-form-label" style="text-align:left;">ماه</label>
                <div class="col-sm-2">
                    <select id="month" class="form-control">
                        @foreach($months as $key=>$month)
                            <option value="{{$key}}">{{$month}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-2">
                    <button type="button" class="btn btn-success" onclick="report()">تهیه گزارش</button>
                </div>

            </div>

            <div id="content"></div>
        </div>
    </div>

    <script>
        function report() {
            var year = $("#year").val(),
                month = $("#month").val(),
                reportType = $("#reportType").val();

            $.ajax('/api/report', {
                type: 'post',
                data: {
                    "type":reportType,
                    "year":year,
                    "month":month
                },
                success: function(response) {
                    console.log(response);
                }
            })
        }
    </script>
@endsection
