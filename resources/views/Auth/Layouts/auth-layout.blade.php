<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title', 'Login | Smart Inventory')</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Smart Inventory — track, manage, and grow your business inventory.">
	<meta name="keywords" content="inventory, billing, sales, purchase, stock management, smart inventory">

	<link rel="shortcut icon" type="image/png" href="{{ asset('template/assets/img/favicon.png') }}?v=2">
	<link rel="icon" type="image/png" href="{{ asset('template/assets/img/favicon.png') }}?v=2">
	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('template/assets/img/apple-touch-icon.png') }}?v=2">
	<link rel="stylesheet" href="{{ asset('template/assets/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('template/assets/plugins/tabler-icons/tabler-icons.min.css') }}">
	<link rel="stylesheet" href="{{ asset('template/assets/css/iconsax.css') }}">
	<link rel="stylesheet" href="{{ asset('template/assets/css/style.css') }}">
	<link rel="stylesheet" href="{{ asset('template/assets/css/global-loader.css') }}">
	<style>
		:root {
			--si-navy: #0b3a6e;
			--si-blue: #1a5f9e;
			--si-green: #2e9e57;
			--si-green-bright: #34b56a;
			--si-muted: #6b7c93;
			--si-border: #d9e2ec;
			--si-label: #1e3a5f;
		}

		.si-auth-page {
			min-height: 100vh;
			background: #f4f7fb;
			position: relative;
			overflow: hidden;
		}

		.si-auth-bg-mark {
			position: absolute;
			left: -2%;
			top: 18%;
			width: min(42vw, 420px);
			opacity: 0.07;
			pointer-events: none;
			user-select: none;
			z-index: 0;
		}

		.si-auth-bg-mark img {
			width: 100%;
			height: auto;
		}

		.si-auth-dots {
			position: absolute;
			width: 72px;
			height: 96px;
			background-image: radial-gradient(circle, #9bb0c9 1.4px, transparent 1.5px);
			background-size: 12px 12px;
			opacity: 0.55;
			pointer-events: none;
			z-index: 0;
		}

		.si-auth-dots--tl {
			top: 28px;
			left: 36px;
		}

		.si-auth-dots--tr {
			top: 28px;
			right: 36px;
		}

		.si-auth-wave {
			position: absolute;
			bottom: -40px;
			width: 280px;
			height: 180px;
			pointer-events: none;
			z-index: 0;
			opacity: 0.85;
		}

		.si-auth-wave--bl {
			left: -40px;
			background:
				radial-gradient(ellipse 90% 70% at 30% 80%, rgba(46, 158, 87, 0.28) 0%, transparent 70%),
				radial-gradient(ellipse 70% 60% at 55% 90%, rgba(11, 58, 110, 0.22) 0%, transparent 72%);
		}

		.si-auth-wave--br {
			right: -50px;
			background:
				radial-gradient(ellipse 90% 70% at 70% 80%, rgba(26, 95, 158, 0.26) 0%, transparent 70%),
				radial-gradient(ellipse 70% 60% at 40% 95%, rgba(46, 158, 87, 0.2) 0%, transparent 72%);
		}

		.si-auth-wrap {
			position: relative;
			z-index: 1;
		}

		.si-auth-card {
			border: 0;
			border-radius: 22px;
			background: #fff;
			box-shadow: 0 18px 50px rgba(15, 40, 80, 0.1);
		}

		.si-auth-card .card-body {
			padding: 2rem 2.1rem 1.5rem;
		}

		.si-auth-brand {
			text-align: center;
			margin-bottom: 1.15rem;
		}

		.si-auth-brand img {
			max-width: 220px;
			width: 100%;
			height: auto;
		}

		.si-auth-title {
			font-size: 1.55rem;
			font-weight: 700;
			text-align: center;
			margin-bottom: 0.25rem;
			color: var(--si-navy);
			letter-spacing: -0.02em;
		}

		.si-auth-subtitle {
			color: var(--si-muted);
			font-size: 0.92rem;
			margin-bottom: 1.5rem;
			text-align: center;
			line-height: 1.45;
		}

		.si-auth-card .form-label {
			font-weight: 600;
			color: var(--si-label);
			font-size: 0.875rem;
			margin-bottom: 0.4rem;
		}

		.si-auth-card .input-group {
			border: 1px solid var(--si-border);
			border-radius: 8px;
			overflow: hidden;
			transition: border-color 0.2s ease, box-shadow 0.2s ease;
			background: #fff;
		}

		.si-auth-card .input-group:focus-within {
			border-color: var(--si-blue);
			box-shadow: 0 0 0 3px rgba(26, 95, 158, 0.12);
		}

		.si-auth-card .input-group-text {
			background: #fff;
			border: 0;
			color: var(--si-blue);
			padding-left: 0.9rem;
			padding-right: 0.55rem;
		}

		.si-auth-card .form-control {
			border: 0;
			background: #fff;
			padding-top: 0.7rem;
			padding-bottom: 0.7rem;
			font-size: 0.93rem;
			color: #1e293b;
		}

		.si-auth-card .form-control::placeholder {
			color: #94a3b8;
		}

		.si-auth-card .form-control:focus {
			box-shadow: none;
		}

		.si-auth-card .pass-group {
			position: relative;
		}

		.si-auth-card .pass-group .toggle-password {
			position: absolute;
			right: 14px;
			top: 50%;
			transform: translateY(-50%);
			z-index: 5;
			cursor: pointer;
			color: #94a3b8;
		}

		.si-auth-card .pass-group .toggle-password:hover {
			color: var(--si-blue);
		}

		.si-auth-options {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 0.75rem;
			margin-bottom: 1.25rem;
			flex-wrap: wrap;
		}

		.si-remember {
			display: inline-flex;
			align-items: center;
			gap: 0.45rem;
			margin: 0;
			cursor: pointer;
			user-select: none;
			color: var(--si-muted);
			font-size: 0.88rem;
			line-height: 1;
		}

		.si-remember .form-check-input {
			float: none;
			margin: 0;
			position: static;
			flex-shrink: 0;
			width: 1rem;
			height: 1rem;
			border-color: #b8c5d6;
			vertical-align: middle;
		}

		.si-remember .form-check-input:checked {
			background-color: var(--si-blue);
			border-color: var(--si-blue);
		}

		.si-remember span {
			line-height: 1;
			padding-top: 1px;
		}

		.si-auth-btn {
			border: 0;
			border-radius: 8px;
			padding: 0.8rem 1rem;
			font-weight: 700;
			font-size: 0.98rem;
			letter-spacing: 0.02em;
			background: linear-gradient(90deg, #0b3a6e 0%, #1a6bb0 42%, #2e9e57 100%);
			box-shadow: 0 10px 22px rgba(26, 95, 158, 0.28);
			transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 0.5rem;
		}

		.si-auth-btn:hover {
			transform: translateY(-1px);
			filter: brightness(1.03);
			box-shadow: 0 14px 28px rgba(26, 95, 158, 0.34);
			color: #fff;
		}

		.si-auth-btn i {
			font-size: 1.05rem;
			line-height: 1;
		}

		.si-auth-footer {
			margin-top: 1.35rem;
			padding-top: 1.1rem;
			border-top: 1px solid #e8eef5;
			text-align: center;
		}

		.si-auth-powered {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 0.45rem;
			flex-wrap: wrap;
			color: var(--si-muted);
			font-size: 0.8rem;
		}

		.si-auth-powered-logo {
			display: inline-flex;
			align-items: center;
			gap: 0.35rem;
			line-height: 1;
			text-decoration: none;
			color: inherit;
		}

		a.si-auth-powered-logo:hover,
		a.si-auth-powered-logo:focus {
			text-decoration: none;
			opacity: 0.9;
		}

		.si-auth-powered-mark {
			width: 22px;
			height: 22px;
			border-radius: 5px;
			background: linear-gradient(145deg, #3b4db8 0%, #5b6fd1 100%);
			color: #fff;
			font-weight: 800;
			font-size: 0.78rem;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-family: Georgia, 'Times New Roman', serif;
			box-shadow: 0 2px 6px rgba(59, 77, 184, 0.35);
		}

		.si-auth-powered-name {
			font-size: 0.82rem;
			font-weight: 700;
			letter-spacing: 0.04em;
		}

		.si-auth-powered-name .ps-priority {
			color: #1e3a8a;
		}

		.si-auth-powered-name .ps-solutions {
			color: var(--si-green);
			font-weight: 600;
			margin-left: 0.2rem;
		}

		@media (max-width: 575.98px) {
			.si-auth-card .card-body {
				padding: 1.5rem 1.2rem 1.25rem;
			}

			.si-auth-brand img {
				max-width: 180px;
			}

			.si-auth-title {
				font-size: 1.35rem;
			}

			.si-auth-dots,
			.si-auth-bg-mark {
				display: none;
			}

			.si-auth-wave {
				width: 180px;
				height: 120px;
				opacity: 0.55;
			}
		}
	</style>
	@stack('styles')
</head>

<body class="bg-white">
	<div id="global-loader" style="display:none;">
		<span class="page-loader"></span>
		<span class="loader-text">Please wait...</span>
	</div>
	<div class="main-wrapper si-auth-page">
		<div class="si-auth-dots si-auth-dots--tl" aria-hidden="true"></div>
		<div class="si-auth-dots si-auth-dots--tr" aria-hidden="true"></div>
		<div class="si-auth-bg-mark" aria-hidden="true">
			<img src="{{ asset('template/assets/img/smart-inventory-logo.png') }}" alt="">
		</div>
		<div class="si-auth-wave si-auth-wave--bl" aria-hidden="true"></div>
		<div class="si-auth-wave si-auth-wave--br" aria-hidden="true"></div>

		<div class="container-fluid si-auth-wrap">
			<div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
				<div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap">
					<div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4 mx-auto">
						<div class="d-flex flex-column justify-content-lg-center py-4 flex-fill">
							@yield('content')
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script src="{{ asset('template/assets/js/jquery-3.7.1.min.js') }}"></script>
	<script src="{{ asset('template/assets/js/bootstrap.bundle.min.js') }}"></script>
	<script src="{{ asset('template/assets/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
	<script src="{{ asset('template/assets/js/script.js') }}"></script>
	<script src="{{ asset('template/assets/js/global-loader.js') }}?v=2"></script>
	@stack('scripts')
</body>

</html>
