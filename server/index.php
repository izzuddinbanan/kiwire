<?php

require_once 'admin/includes/include_config.php';
require_once 'admin/includes/include_general.php';

?>



<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?=sync_brand_decrypt(SYNC_PRODUCT) . " " . sync_brand_decrypt(SYNC_TITLE)?><?=isset($kiw['page']) ? " | {$kiw['page']}" : '' ?></title>

    <link rel="apple-touch-icon" href="/assets/images/<?= sync_brand_decrypt(SYNC_ICON) ?>">
    <link rel="shortcut icon" type="image/x-icon" href="/assets/images/<?= sync_brand_decrypt(SYNC_ICON) ?>">
</head>

<body style="padding: 0; margin: 0; background: url('/assets/images/kiwire-background.jpg'); overflow: hidden;">

    <div style="width: 100%; position: absolute; height: 500px; padding: 0;">

        <!-- <img src="/assets/images/kiwire-logo.png" style="display: block; margin: 100px auto; width: 50%; max-width: 400px;"> -->
        <a href="/admin">
            <img src="/assets/images/<?= sync_brand_decrypt(SYNC_LOGO_BIG) ?>" style="display: block; margin: 100px auto; width: 50%; max-width: 400px;">
        </a>

    </div>

    <div class="background-net" style="height: 800px; width: 100%;"></div>

</body>

<script src="app-assets/js/core/libraries/jquery.min.js"></script>
<script src="/assets/js/jquery.particleground.js"></script>

<script>

    $(document).ready(function () {

        let particle_space = $('.background-net');

        particle_space.css("height", $(window).height());

        particle_space.particleground({
            dotColor: '#5e676d',
            lineColor: '#5e676d',
            density: 10000,
            proximity: 100,
            parallax: false
        });

    });

</script>

</html>
