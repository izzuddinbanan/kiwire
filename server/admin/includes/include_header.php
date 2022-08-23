<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

    <title><?=sync_brand_decrypt(SYNC_PRODUCT) . " " . sync_brand_decrypt(SYNC_TITLE)?><?=isset($kiw['page']) ? " | {$kiw['page']}" : '' ?></title>

    <!-- <link rel="apple-touch-icon" href="/app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico"> -->

    <!-- <link rel="apple-touch-icon" href="/volt-theme/assets/img/favicon/kiwire-icon-removebg.png">
    <link rel="shortcut icon" type="image/x-icon" href="/volt-theme/assets/img/favicon/kiwire-icon-removebg.png"> -->

    <link rel="apple-touch-icon" href="../../assets/images/<?= sync_brand_decrypt(SYNC_ICON) ?>">
    <link rel="shortcut icon" type="image/x-icon" href="../../assets/images/<?= sync_brand_decrypt(SYNC_ICON) ?>">

    <link href="/libs/google-fonts/montserrat/montserrat.css" rel="stylesheet">
    <!-- <link href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css" rel="stylesheet" /> -->

    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/ui/prism.min.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/bootstrap-extended.css">

    <link rel="stylesheet" type="text/css" href="/app-assets/css/colors.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/components.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/themes/dark-layout.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/themes/semi-dark-layout.css">

    <link rel="stylesheet" type="text/css" href="/app-assets/css/core/menu/menu-types/vertical-menu.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/core/colors/palette-gradient.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/toastr.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/forms/spinner/jquery.bootstrap-touchspin.css">

    <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/parsley.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/pickers/pickadate/pickadate.css">
    
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/forms/select/select2.min.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/charts/apexcharts.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/editors/quill/quill.snow.css">  
    
    <link rel="stylesheet" type="text/css" href="/app-assets/dropify/dist/css/dropify.min.css">

    <?php if (file_exists(dirname(__FILE__, 2) . "/stylesheets/{$kiw['name']}.css")){ ?>
    <link rel="stylesheet" type="text/css" href="/admin/stylesheets/<?=$kiw['name']?>.css">
    <?php } ?>

    <?php 
    if($_SESSION['style']){
        if (is_dir(dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/stylesheets")){ ?>

            <link rel="stylesheet" type="text/css" href="/custom/<?= $_SESSION['tenant_id'] ?>/stylesheets/app.css">
        
            <?php if (file_exists(dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/stylesheets/{$kiw['name']}.css")){ ?>

            <link rel="stylesheet" type="text/css" href="/custom/<?= $_SESSION['tenant_id'] ?>/stylesheets/<?=$kiw['name']?>.css">

    <?php
            }
        } 
    } ?>

</head>
<style>
    @media only screen and (max-device-width: 767px) {
        .content-header-title{
            font-size: 100% !important;
            margin: 10px !important;
            font-weight: 500 !important;
        }
    }
</style>

<body class="vertical-layout vertical-menu-modern 2-columns <?= ($_SESSION['theme'] == "dark" ? "dark-layout" : "")?> navbar-sticky <?= ($_SESSION['menu_toggle'] == false ? "menu-collapsed" : "menu-expanded") ?> footer-static" data-open="click" data-menu="vertical-menu-modern" data-col="2-columns">
