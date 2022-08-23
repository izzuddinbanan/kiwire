<?php

global $kiw_banner;

// load background image

$kiw_background = strtolower($_SESSION['user']['class']);

if ($kiw_background == "desktop") $kiw_background = $kiw_temp['bg_lg'];
elseif ($kiw_background == "tablet") $kiw_background = $kiw_temp['bg_md'];
else $kiw_background = $kiw_temp['bg_sm'];

if (empty($kiw_background)){

    foreach (array("bg_lg", "bg_md", "bg_sm") as $kiw_bg_select){

        if (!empty($kiw_temp[$kiw_bg_select])){

            $kiw_background = $kiw_temp[$kiw_bg_select];

        }

    }

}


if (substr($kiw_temp['bg_css'], -1) != ";"){

    $kiw_temp['bg_css'] .= ";";

}


?>
<!doctype html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <?php if ($kiw_banner['status'] == "y"){ ?>

    <meta name="smartbanner:title" content="<?= $kiw_banner['app_title'] ?>">
    <meta name="smartbanner:author" content="<?= $kiw_banner['app_author'] ?>">
    <meta name="smartbanner:price" content="<?= $kiw_banner['app_price'] ?>">
    <meta name="smartbanner:price-suffix-apple" content="  - In the App Store">
    <meta name="smartbanner:price-suffix-google" content=" - In Google Play">
    <meta name="smartbanner:icon-apple" content="/custom/<?= $_SESSION['controller']['tenant_id'] ?>/images/<?= $kiw_banner['app_logopath'] ?>">
    <meta name="smartbanner:icon-google" content="/custom/<?= $_SESSION['controller']['tenant_id'] ?>/images/<?= $kiw_banner['app_logopath'] ?>">
    <meta name="smartbanner:button" content="View">
    <meta name="smartbanner:button-url-apple" content="<?= $kiw_banner['app_appstore_url'] ?>">
    <meta name="smartbanner:button-url-google" content="<?= $kiw_banner['app_playstore_url'] ?>">
    <meta name="smartbanner:enabled-platforms" content="android,ios">
    <meta name="smartbanner:close-label" content="Close">

    <link rel="stylesheet" href="/libs/smartbanner/smartbanner.css">
    <script src="/libs/smartbanner/smartbanner.js"></script>

    <?php } ?>

    <title><?= (empty($kiw_temp['title']) ? "Wifi Login" : $kiw_temp['title']) ?></title>

</head>

<?php if ($kiw_temp['purpose'] == "campaign"){ ?>

<link href="/libs/mightyslider/src/css/mightyslider.css" rel="stylesheet">
<link href="/libs/mightyslider/assets/js/ilightbox/css/ilightbox.css" rel="stylesheet" />

<?php } ?>

<link rel="stylesheet" type="text/css" href="/app-assets/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="/app-assets/css/bootstrap-extended.css">
<link rel="stylesheet" type="text/css" href="/app-assets/css/colors.css">
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/vendors.min.css">

<link rel="stylesheet" type="text/css" href="/admin/designer/contentbuilder/contentbuilder.css">
<link rel="stylesheet" type="text/css" href="/admin/designer/assets/minimalist-blocks/content.css">

<link rel="stylesheet" type="text/css" href="/admin/designer/assets/scripts/slick/slick.css" />
<link rel="stylesheet" type="text/css" href="/admin/designer/assets/scripts/slick/slick-theme.css" />

<link rel="stylesheet" type="text/css" href="/assets/css/parsley.css" />

<style>

    html {

        height: 100%;

    }

    body {

        min-height: 100%;
        min-width: 100%;

    <?php if (!empty($kiw_background)){ ?>
    background-image: url(<?= $kiw_background ?>);
    <?php } ?>
    <?php if (!empty($kiw_temp['bg_css'])){ ?>
    <?= $kiw_temp['bg_css'] ?>
    <?php } ?>


    }

    .cb-container {

        overflow: hidden;
        padding: 5px;
        min-height: 100%;
        min-width: 100%;

    }

</style>

<?php if ($kiw_temp['purpose'] !== "campaign"){ ?>

<script src="/app-assets/vendors/js/vendors.min.js"></script>
<!--<script type="application/javascript" src="/assets/js/jquery-3.5.1.min.js"></script>-->

<?php } ?>


<body>

<div class="cb-container">


