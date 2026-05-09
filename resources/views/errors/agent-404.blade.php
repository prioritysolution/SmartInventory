<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>Page Not Found</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('agenttemplate/assets/img/favicon.png') }}">
<link rel="stylesheet" href="{{ asset('agenttemplate/assets/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('agenttemplate/assets/css/animate.css') }}">
<link rel="stylesheet" href="{{ asset('agenttemplate/assets/plugins/fontawesome/css/fontawesome.min.css') }}">
<link rel="stylesheet" href="{{ asset('agenttemplate/assets/plugins/fontawesome/css/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('agenttemplate/assets/css/style.css') }}">
</head>
<body class="error-page">
<div id="global-loader">
    <div class="whirly-loader"></div>
</div>
<div class="main-wrapper">
    <div class="error-box">
        <h1>404</h1>
        <h3 class="h2 mb-3"><i class="fas fa-exclamation-circle"></i> Oops! Page not found!</h3>
        <p class="h4 font-weight-normal">The page you requested was not found.</p>
        <a href="{{ route('agent.dashboard') }}" class="btn btn-primary">Back to Home</a>
    </div>
</div>
<script src="{{ asset('agenttemplate/assets/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('agenttemplate/assets/js/feather.min.js') }}"></script>
<script src="{{ asset('agenttemplate/assets/js/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('agenttemplate/assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('agenttemplate/assets/js/script.js') }}"></script>
</body>
</html>
