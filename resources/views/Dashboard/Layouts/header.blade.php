<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin Dashboard | Kanakku</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Kanakku Admin Dashboard">
    <meta name="keywords" content="admin, invoice, dashboard">
    <meta name="author" content="Dreams Technologies">

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
    </style>

</head>

<body>
    <div class="main-wrapper">
