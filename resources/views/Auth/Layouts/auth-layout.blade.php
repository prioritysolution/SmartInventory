<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Login | Kanakku - Invoice and Billing Management Admin Dashboard Template</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Kanakku is a Sales, Invoices & Accounts Admin template for Accountant or Companies/Offices with various features for all your needs. Try Demo and Buy Now.">
	<meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern, accounts, invoice, html5, responsive, CRM, Projects">
	<meta name="author" content="Dreams Technologies">

	<link rel="shortcut icon" type="image/x-icon" href="{{asset('template/assets/img/favicon.png')}}">
	<link rel="apple-touch-icon" sizes="180x180" href="{{asset('template/assets/img/apple-touch-icon.png')}}">
	<link rel="stylesheet" href="{{asset('template/assets/css/bootstrap.min.css')}}">
	<link rel="stylesheet" href="{{asset('template/assets/plugins/tabler-icons/tabler-icons.min.css')}}">
	<link rel="stylesheet" href="{{asset('template/assets/css/iconsax.css')}}">
	<link rel="stylesheet" href="{{asset('template/assets/css/style.css')}}">
</head>

<body class="bg-white">
	<div class="main-wrapper auth-bg">
		<div class="container-fuild">
			<div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
				<div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap ">
					<div class="col-lg-4 mx-auto">
						<div class="d-flex flex-column justify-content-lg-center p-4 p-lg-0 pb-0 flex-fill">
							@yield('content')
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<script src="{{asset('template/assets/js/jquery-3.7.1.min.js')}}"></script>
	<script src="{{asset('template/assets/js/bootstrap.bundle.min.js')}}"></script>
	<script src="{{asset('template/assets/plugins/sweetalert/sweetalert2.all.min.js')}}"></script>
	<script src="{{asset('template/assets/js/script.js')}}"></script>
	@stack('scripts')
</body>
</html>
