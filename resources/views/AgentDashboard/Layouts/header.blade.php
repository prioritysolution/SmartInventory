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

<link rel="shortcut icon" type="image/x-icon" href="{{asset('agenttemplate/assets/img/favicon.png')}}">

<link rel="stylesheet" href="{{asset('agenttemplate/assets/css/bootstrap.min.css')}}">

<link rel="stylesheet" href="{{asset('agenttemplate/assets/css/animate.css')}}">

<link rel="stylesheet" href="{{asset('agenttemplate/assets/css/dataTables.bootstrap4.min.css')}}">

<link rel="stylesheet" href="{{asset('agenttemplate/assets/plugins/fontawesome/css/fontawesome.min.css')}}">
<link rel="stylesheet" href="{{asset('agenttemplate/assets/plugins/fontawesome/css/all.min.css')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="{{asset('template/assets/css/bootstrap-datetimepicker.min.css')}}">

<link rel="stylesheet" href="{{asset('agenttemplate/assets/css/style.css')}}">

<style>
    .header .header-left,
    .header .header-left.active {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: 4px 28px 4px 8px !important;
        overflow: hidden;
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
</style>

@stack('style')

</head>
<body>
<div class="main-wrapper">
