<!DOCTYPE html>
<html lang="en-IN">

<head>

    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Smart Inventory')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Smart Inventory Dashboard">
    <meta name="keywords" content="inventory, sales, purchase, dashboard">

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('template/assets/img/favicon.png') }}">

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('template/assets/img/apple-touch-icon.png') }}">

    <!-- Theme Script -->
    <script src="{{ asset('template/assets/js/theme-script.js') }}"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('template/assets/css/bootstrap.min.css') }}">

    <!-- Fontawesome -->
    <link rel="stylesheet" href="{{ asset('template/assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/plugins/fontawesome/css/all.min.css') }}">

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="{{ asset('template/assets/plugins/tabler-icons/tabler-icons.min.css') }}">

    <!-- Date Range Picker -->
    <link rel="stylesheet" href="{{ asset('template/assets/plugins/daterangepicker/daterangepicker.css') }}">

    <!-- Datetimepicker -->
    <link rel="stylesheet" href="{{ asset('template/assets/css/bootstrap-datetimepicker.min.css') }}">

    <!-- Simplebar -->
    <link rel="stylesheet" href="{{ asset('template/assets/plugins/simplebar/simplebar.min.css') }}">

    <!-- Iconsax -->
    <link rel="stylesheet" href="{{ asset('template/assets/css/iconsax.css') }}">

    <!-- Main Style -->
    <link rel="stylesheet" href="{{ asset('template/assets/css/style.css') }}">

    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- Custom DataTables Styles -->
    <link rel="stylesheet" href="{{ asset('template/assets/css/custom-datatable.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">



    @stack('style')
    <style>
        body {
            background-color: #edeef3 !important;
            /* #f7f8f9 */
        }

        .page-wrapper .content {
            padding-top: 8px !important;
        }

        .header {
            height: 70px !important;
        }

        .page-wrapper {
            padding-top: 70px !important;
        }

        .sidebar .sidebar-logo.custom-logo-fix {
            height: 70px !important;
            width: 276px;
            padding: 8px 36px 8px 12px !important;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            background-color: #f7f8f9;
        }

        .sidebar [data-simplebar],
        .sidebar .slimScrollDiv {
            top: 70px !important;
            height: calc(100% - 70px) !important;
        }

        .sidebar .sidebar-inner {
            margin-top: 0 !important;
        }

        .custom-logo-fix .logo-normal img,
        .custom-logo-fix .dark-logo img {
            max-height: 48px;
            max-width: 210px;
            width: auto;
            height: 48px;
            object-fit: contain;
            object-position: left center;
            display: block;
            margin-left: 0;
            background-color: transparent;
        }

        .custom-logo-fix .logo-small img,
        .custom-logo-fix .dark-small img {
            max-height: 36px;
            max-width: 70px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
            background-color: transparent;
        }

        .header-left .logo img,
        .header-left .dark-logo img {
            max-height: 48px;
            width: auto;
            max-width: 210px;
            height: 48px;
            object-fit: contain;
            display: block;
            background-color: transparent;
        }

        @media (max-width: 991.98px) {
            .header-left .logo img,
            .header-left .dark-logo img {
                width: auto !important;
                max-width: 180px;
                max-height: 40px;
                height: 40px;
            }
        }

        .sidebar .sidebar-menu > ul > li > a > i {
            font-size: 18px;
            min-width: 22px;
            flex-shrink: 0;
            line-height: 1.2;
            margin-top: 2px;
        }
    </style>

</head>

<body>
    <div class="main-wrapper">
