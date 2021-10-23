<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{config('app.name')}} @if(isset($title)) | {{$title}} @endif</title>

        <!-- Styles -->
        <link rel="stylesheet" href="/css/styles.css" type="text/css">
        <link rel="stylesheet" href="/css/switch.css" type="text/css">
        <link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="/vendor/fontawesome-free/css/all.min.css" type="text/css">
        <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
        <script src="/js/bootstrap.min.js" type="text/javascript"></script>
    </head>

    <body class="x">
    <div class="loader">
        <div class="row" style="text-align:center;position:relative;top:40%;">
            <div class="col-md-2 mx-auto">
                <div class="card">
                    <div class="card-body" style="padding:50px 0;">
                        <div>لطفا کمی صبر کنید ...</div>
                        <br>
                        <div style="text-align:center;">
                            <img src="/images/loader.gif" width="64" height="22">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
        <div class="container-fluid" style="border-radius:5px;height:100vh;padding:0;">
            <div class="mynav">
                <div class="container" id="menu-container">
                    <ul>
                        <li id="close-menu-btn" style="display: none;"><a href="#" onclick="closeMenu()" style="font-size:20px;">&times;</a></li>
                        <li><a href="#" class="inactive">شناسه 1001</a></li>
                        <li><a class="@if(isset($active) && $active == 'home') active @else inactive @endif" href="{{route('site.home')}}">وضعیت شیر</a></li>
                        <li><a class="@if(isset($active) && $active == 'settings') active @else inactive @endif" href="{{route('site.settings')}}">وضعیت آب</a></li>
                        <li><a class="@if(isset($active) && $active == 'reports') active @else inactive @endif" href="{{route('site.reports')}}">گزارشات</a></li>
                        <li><a class="@if(isset($active) && $active == 'about') active @else inactive @endif" href="{{route('site.about')}}">درباره</a></li>
                        <!--<li><a href="#" class="inactive">اپلیکیشن تلفن همراه</a></li>-->
                        <li><a href="{{route('site.logout')}}" class="inactive">خروج</a></li>
                    </ul>
                </div>
            </div>

            <div class="toggle-bar">
                <ul>
                    <li><a href="#" onclick="openMenu()" style="font-size: 20px;">&#9776;</a></li>
                </ul>
            </div>

            <div class="content">
                <div class="container">
                    @yield('content')
                </div>
            </div>
        </div>

    <script>
        function openMenu() {
            document.getElementsByClassName("mynav")[0].style.display = "block";
            document.getElementById("close-menu-btn").style.display = "block";
            document.getElementById("menu-container").classList.remove("container");
        }
        function closeMenu() {
            document.getElementsByClassName("mynav")[0].style.display = "none";
            document.getElementById("close-menu-btn").style.display = "none";
            document.getElementById("menu-container").classList.add("container");
        }
    </script>
    </body>
</html>
