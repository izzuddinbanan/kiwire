<?php

require_once dirname(__FILE__, 3) . "/admin/includes/include_config.php";

global $kiw_db, $kiw_tenant, $kiw_page;


if (empty($kiw_cpanel)) {

    $kiw_cpanel = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_cpanel_template WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");
}


?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>User Control Panel | <?= $kiw_page ?></title>

    <!-- <link rel="apple-touch-icon" href="../../cpanel/assets/img/um-logo.png"> -->
    <link rel="shortcut icon" type="image/x-icon" href="../../assets/images/<?= sync_brand_decrypt(SYNC_ICON) ?>">

    <link rel="stylesheet" type="text/css" href="../../../cpanel/assets/css/bootstrap.css">
    <!-- <link rel="stylesheet" type="text/css" href="../../../app-assets/css/bootstrap-extended.css"> -->

    <link rel="stylesheet" type="text/css" href="assets/lib/perfect-scrollbar/css/perfect-scrollbar.min.css" />
    <link rel="stylesheet" type="text/css" href="assets/lib/material-design-icons/css/material-design-iconic-font.min.css" />
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.6.9/sweetalert2.min.css"> -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.css">


    <!-- begin added css -->

    <link rel="stylesheet" type="text/css" href="../../../../app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="../../../../app-assets/vendors/css/charts/apexcharts.css">
    <link rel="stylesheet" type="text/css" href="../../../../app-assets/vendors/css/extensions/tether-theme-arrows.css">
    <link rel="stylesheet" type="text/css" href="../../../../app-assets/vendors/css/extensions/tether.min.css">
    <link rel="stylesheet" type="text/css" href="../../../../app-assets/vendors/css/extensions/shepherd-theme-default.css">
    <link rel="stylesheet" type="text/css" href="../../../../app-assets/fonts/feather/iconfont.css">



    <link rel="stylesheet" type="text/css" href="../../../../app-assets/css/colors.css">
    <link rel="stylesheet" type="text/css" href="../../../../app-assets/css/components.css">
    <link rel="stylesheet" type="text/css" href="../../../../app-assets/css/themes/dark-layout.css">
    <link rel="stylesheet" type="text/css" href="../../../../app-assets/css/themes/semi-dark-layout.css">

    <link rel="stylesheet" type="text/css" href="../../../../app-assets/css/core/menu/menu-types/vertical-menu.css">
    <link rel="stylesheet" type="text/css" href="../../../../app-assets/css/core/colors/palette-gradient.css">
    <link rel="stylesheet" type="text/css" href="../../../../app-assets/css/pages/dashboard-analytics.css">
    <link rel="stylesheet" type="text/css" href="../../../../app-assets/css/pages/dashboard-ecommerce.css">
    <link rel="stylesheet" type="text/css" href="../../../../app-assets/css/pages/card-analytics.css">

    <link rel="stylesheet" type="text/css" href="../assets/css/parsley.css">


    <!--end  -->


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>


    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.6.9/sweetalert2.min.js"></script> -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.js"></script>






    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- <link rel="stylesheet" type="text/css" href="assets/daterangepicker/css/daterangepicker.css" />
    <link rel="stylesheet" type="text/css" href="assets/datetimepicker/css/bootstrap-datetimepicker.css" />
    <link rel="stylesheet" type="text/css" href="assets/datetimepicker/css/bootstrap-datetimepicker.min.css" /> -->

    <!-- <link rel="stylesheet" type="text/css" href="../app-assets/css/daterangepicker.css"> -->
    <link rel="stylesheet" type="text/css" href="../app-assets/vendors/css/pickers/pickadate/pickadate.css">


    <link rel="stylesheet" type="text/css" href="assets/lib/datatables/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="assets/lib/datatables/css/dataTables.responsive.min.css">
    <link rel="stylesheet" type="text/css" href="assets/lib/jquery-ui/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="assets/lib/toastr/toastr.min.css" />

    <link rel="stylesheet" href="assets/css/style.css" type="text/css" />


</head>

<body>