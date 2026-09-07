<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<meta name="description" content="POS - Bootstrap Admin Template">
<meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, invoice, html5, responsive, Projects">
<meta name="author" content="Dreamguys - Bootstrap Admin Template">
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Login - Pos admin template</title>

<link rel="shortcut icon" type="image/png" href="{{asset('agenttemplate/assets/img/favicon.png')}}?v=2">
<link rel="icon" type="image/png" href="{{asset('agenttemplate/assets/img/favicon.png')}}?v=2">
<link rel="apple-touch-icon" sizes="180x180" href="{{asset('agenttemplate/assets/img/apple-touch-icon.png')}}?v=2">

<link rel="stylesheet" href="{{asset('agenttemplate/assets/css/bootstrap.min.css')}}">

<link rel="stylesheet" href="{{asset('agenttemplate/assets/plugins/fontawesome/css/fontawesome.min.css')}}">
<link rel="stylesheet" href="{{asset('agenttemplate/assets/plugins/fontawesome/css/all.min.css')}}">

<link rel="stylesheet" href="{{asset('agenttemplate/assets/css/style.css')}}">
<link rel="stylesheet" href="{{ asset('template/assets/css/global-loader.css') }}">
</head>
<body class="account-page">
<div id="global-loader" style="display:none;">
    <span class="page-loader"></span>
    <span class="loader-text">Please wait...</span>
</div>

<div class="main-wrapper">
<div class="account-content">
<div class="login-wrapper">
@yield('content')
<div class="login-img">
<img src="{{asset('agenttemplate/assets/img/login.jpg')}}" alt="img">
</div>
</div>
</div>
</div>


<script src="{{asset('agenttemplate/assets/js/jquery-3.6.0.min.js')}}"></script>

<script src="{{asset('agenttemplate/assets/js/feather.min.js')}}"></script>

<script src="{{asset('agenttemplate/assets/js/bootstrap.bundle.min.js')}}"></script>

<script src="{{asset('agenttemplate/assets/js/script.js')}}"></script>
<script src="{{asset('template/assets/js/global-loader.js')}}?v=2"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('scripts')
</body>
</html>