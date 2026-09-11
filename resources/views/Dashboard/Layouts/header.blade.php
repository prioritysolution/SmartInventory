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
    <link rel="shortcut icon" type="image/png" href="{{ asset('template/assets/img/favicon.png') }}?v=2">
    <link rel="icon" type="image/png" href="{{ asset('template/assets/img/favicon.png') }}?v=2">

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('template/assets/img/apple-touch-icon.png') }}?v=2">

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
    <link rel="stylesheet" href="{{ asset('template/assets/css/global-loader.css') }}">

    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- Custom DataTables Styles -->
    <link rel="stylesheet" href="{{ asset('template/assets/css/custom-datatable.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">



    @stack('style')
    <script>
        const IS_ADMIN = {{ session('is_admin') ? 'true' : 'false' }};
    </script>
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
            max-height: 58px;
            max-width: 250px;
            width: auto;
            height: 58px;
            object-fit: contain;
            object-position: left center;
            display: block;
            margin-left: 0;
            background-color: transparent;
        }

        .custom-logo-fix .logo-small img,
        .custom-logo-fix .dark-small img {
            max-height: 42px;
            max-width: 80px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
            background-color: transparent;
        }

        .header-left .logo img,
        .header-left .dark-logo img {
            max-height: 58px;
            width: auto;
            max-width: 250px;
            height: 58px;
            object-fit: contain;
            display: block;
            background-color: transparent;
        }

        @media (max-width: 991.98px) {
            .header-left .logo img,
            .header-left .dark-logo img {
                width: auto !important;
                max-width: 200px;
                max-height: 48px;
                height: 48px;
            }
        }

        .sidebar .sidebar-menu > ul > li > a > i {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
            font-size: 16px;
            min-width: 22px;
            flex-shrink: 0;
            line-height: 1.2;
            margin-top: 0;
            color: #5b6b7c;
        }

        .report-scroll,
        .report-scroll-sm {
            overflow: auto;
        }
        .report-scroll {
            max-height: calc(100vh - 280px);
        }
        .report-scroll-sm {
            max-height: 260px;
        }
        .report-scroll table,
        .report-scroll-sm table {
            margin-bottom: 0;
        }
        .report-scroll thead th,
        .report-scroll-sm thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f6f6f6;
            box-shadow: 0 1px 0 #dee2e6;
        }
        .report-scroll tfoot th,
        .report-scroll-sm tfoot th {
            position: sticky;
            bottom: 0;
            z-index: 2;
            background: #fff;
            box-shadow: 0 -1px 0 #dee2e6;
        }

        .modal .btn-close {
            opacity: 0.75;
            filter: none;
        }

        .modal .btn-close:hover {
            opacity: 1;
        }

        /* Keep left-side header menus under their trigger (template forces right:0) */
        @media (min-width: 992px) {
            .header .header-user .user-menu #header-search .dropdown-menu,
            .header .header-user .user-menu .add-menu-dropdown {
                left: 0 !important;
                right: auto !important;
                top: 100% !important;
                min-width: 280px;
                max-height: min(70vh, 520px);
                overflow-y: auto;
            }
        }

        .header .header-user .user-menu .add-menu-dropdown .dropdown-item {
            white-space: normal;
            align-items: flex-start;
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .header .header-user .user-menu .add-menu-dropdown .qa-link-name {
            font-weight: 600;
            color: #172b4c;
            line-height: 1.2;
        }

        .header .header-user .user-menu .add-menu-dropdown .qa-link-group {
            display: block;
            font-size: 11px;
            color: #6c757d;
            margin-top: 2px;
        }

        .header .notification_item .badge.notification-count {
            width: auto;
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            top: 2px;
            right: 2px;
            font-size: 10px;
            line-height: 16px;
            border-radius: 999px;
            display: none;
        }

        .header .notification_item .badge.notification-count.is-visible {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>

</head>

<body>
    <div id="global-loader" style="display:none;">
        <span class="page-loader"></span>
        <span class="loader-text">Please wait...</span>
    </div>
    <div class="main-wrapper">
