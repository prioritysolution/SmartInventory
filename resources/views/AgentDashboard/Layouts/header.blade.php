<!DOCTYPE html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<meta name="description" content="POS - Bootstrap Admin Template">
<meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern,  html5, responsive">
<meta name="author" content="Dreamguys - Bootstrap Admin Template">
<meta name="robots" content="noindex, nofollow">
<title>Agent Dashboard | Smart Inventory</title>

<link rel="shortcut icon" type="image/png" href="{{asset('agenttemplate/assets/img/favicon.png')}}?v=2">
<link rel="icon" type="image/png" href="{{asset('agenttemplate/assets/img/favicon.png')}}?v=2">
<link rel="apple-touch-icon" sizes="180x180" href="{{asset('agenttemplate/assets/img/apple-touch-icon.png')}}?v=2">

<link rel="stylesheet" href="{{asset('agenttemplate/assets/css/bootstrap.min.css')}}">

<link rel="stylesheet" href="{{asset('agenttemplate/assets/css/animate.css')}}">

<link rel="stylesheet" href="{{asset('agenttemplate/assets/css/dataTables.bootstrap4.min.css')}}">

<link rel="stylesheet" href="{{asset('agenttemplate/assets/plugins/fontawesome/css/fontawesome.min.css')}}">
<link rel="stylesheet" href="{{asset('agenttemplate/assets/plugins/fontawesome/css/all.min.css')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="{{asset('template/assets/css/bootstrap-datetimepicker.min.css')}}">

<link rel="stylesheet" href="{{asset('agenttemplate/assets/css/style.css')}}">
<link rel="stylesheet" href="{{ asset('template/assets/css/global-loader.css') }}">

<style>
    .header .header-left,
    .header .header-left.active {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: 4px 12px 4px 8px !important;
        overflow: hidden;
    }
    .header #toggle_btn,
    .header-left #toggle_btn,
    #toggle_btn:before,
    #toggle_btn:after,
    .header-left.active #toggle_btn:after {
        display: none !important;
    }
    .header .header-left .logo {
        line-height: 0;
    }
    .header .header-left .logo img {
        width: auto !important;
        height: 56px !important;
        max-width: 240px;
        object-fit: contain;
        object-position: left center;
        background: transparent;
        display: block;
    }
    .header .header-left .logo-small img {
        width: auto !important;
        height: 40px !important;
        max-width: 70px;
        object-fit: contain;
        background: transparent;
        display: block;
    }
    .sidebar .sidebar-menu > ul > li.submenu ul li a,
    .sidebar .sidebar-menu > ul > li.submenu.active ul li a,
    .sidebar .sidebar-menu > ul > li.active.submenu ul li a,
    .sidebar .sidebar-menu > ul > li.submenu ul li a span {
        color: #67748e !important;
        background: transparent !important;
        font-size: 14px !important;
    }
    .sidebar .sidebar-menu > ul > li.submenu ul li a.active,
    .sidebar .sidebar-menu > ul > li.submenu ul li a.active span,
    .sidebar .sidebar-menu > ul > li.submenu ul li a:hover,
    .sidebar .sidebar-menu > ul > li.submenu ul li a:hover span {
        color: #ff9f43 !important;
    }
    .sidebar .sidebar-menu > ul > li > a i {
        width: 20px;
        font-size: 16px;
        margin-right: 10px;
        text-align: center;
        color: #67748e;
    }
    .sidebar .sidebar-menu > ul > li.active > a i,
    .sidebar .sidebar-menu > ul > li.submenu.active > a i,
    .sidebar .sidebar-menu > ul > li > a:hover i {
        color: #ff9f43;
    }
    .sidebar .sidebar-menu > ul > li.submenu ul li a i {
        font-size: 6px;
        width: 10px;
        margin-right: 8px;
        margin-top: 2px;
        color: #67748e;
    }
    .sidebar .sidebar-menu > ul > li.submenu ul li a.active i,
    .sidebar .sidebar-menu > ul > li.submenu ul li a:hover i {
        color: #ff9f43 !important;
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

        /* Agent theme CSS clears btn-close background image — restore visible X */
        .modal .btn-close {
            background-image: none !important;
            opacity: 1 !important;
            position: relative;
            flex-shrink: 0;
        }

        .modal .btn-close::after {
            content: "\00d7";
            font-size: 24px;
            font-weight: 700;
            color: #ea5455;
            line-height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
        }

        .modal .btn-close:hover {
            background: #ea5455 !important;
        }

        .modal .btn-close:hover::after {
            color: #fff;
        }

        .modal .close {
            opacity: 1 !important;
        }

        .modal .close span {
            font-size: 24px;
            font-weight: 700;
            line-height: 22px;
            color: #ea5455;
        }

        .modal .close:hover {
            background: #ea5455 !important;
            border-radius: 50px;
        }

        .modal .close:hover span {
            color: #fff;
        }
    </style>

@stack('style')

</head>
<body>
<div id="global-loader" style="display:none;">
    <span class="page-loader"></span>
    <span class="loader-text">Please wait...</span>
</div>
<div class="main-wrapper">
