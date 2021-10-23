<!DOCTYPE html>
<html lang="en" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Navid Hero">

    <title>ورود به سامانه آبیاری هوشمند</title>

    <link href="/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="/css/styles.css" rel="stylesheet">

</head>
<body class="bg-gradient-primary">
<div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-3"></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">سامانه آبیاری هوشمند</h1>
                                </div>
                                <form method="POST" action="{{ route('site.postLogin') }}" class="user" >
                                    @csrf
                                    <div class="form-group">
                                        <input class="form-control form-control-user" name="device_code" id="device_code" aria-describedby="deviceCodeHelp" value="{{ old('device_code') }}" placeholder="شناسه دستگاه را وارد کنید" required autofocus >
                                    </div>

                                    <div class="form-group">
                                        <input type="password" class="form-control form-control-user" name="password" id="password" placeholder="رمز عبور" required>
                                    </div>

                                    @if(\Illuminate\Support\Facades\Session::get('error'))
                                        <div class="alert alert-danger" style="text-align: center;">
                                            {{\Illuminate\Support\Facades\Session::get('error')}}
                                        </div>
                                    @endif

                                    <button type="submit" class="btn btn-primary btn-user btn-block">
                                        ورود
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
