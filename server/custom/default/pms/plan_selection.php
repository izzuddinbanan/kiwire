<html>

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

    <title>Kiwire Admin | Login</title>

    <link rel="apple-touch-icon" href="/app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/ui/prism.min.css">

    <link rel="stylesheet" type="text/css" href="/app-assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/colors.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/components.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/themes/dark-layout.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/themes/semi-dark-layout.css">

    <link rel="stylesheet" type="text/css" href="/app-assets/css/core/menu/menu-types/vertical-menu.css">

    <link rel="stylesheet" type="text/css" href="/assets/css/style.css">

    <style>
        .button {
            background-color: transparent;
            border: 3px solid green;
            font-size: 16px;
            color: black;
            padding: 5px 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            margin: 6px 2px;
            cursor: pointer;
            border-radius: 20px;
        }

        .button:hover {
            background-color: green;
        }
    </style>

</head>

<body class="vertical-layout vertical-menu-modern 1-column  navbar-floating footer-static bg-full-screen-image  blank-page blank-page" data-open="click" data-menu="vertical-menu-modern" data-col="1-column"><br>

    <div align="center">
        <h1>Dear Guest,</h1>
        <p>Welcome to the XX Wi-Fi Service. For your convenience, we offer Wi-Fi coverage across the hotel.</p>
        <p>All plans work by the clock from the first sign-up.</p>
    </div><br>

    <div class="col-md-6 offset-md-3 col-sm-12">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-title" align="center">
                        <h4 class="mb-0"><b style="color: rgb(46, 125, 50);">BILLING PLAN SELECTION</b></h4><br>

                        <form method="post" action="/custom/{tenant}/pms/login/">

                            <div class="row">

                                <div class="col-md-6">

                                    <h4 class="mb-0"><b style="color: rgb(46, 125, 50); font-size: 17px;">FREE INTERNET</b></h4><br>
                                    <h5 class="mb-0"><b style="color: rgb(46, 125, 50); font-size: 12px;">Recommended for basic surfing & mails, plan available for one device only.</b></h5><br>

                                    <input type="radio" id="24hrs" name="24hrs" value="24hrs">
                                     <label for="24hrs" class="mb-0"><b style="color: rgb(46, 125, 50); font-size: 12px;">24 Hours</b></label>

                                </div>

                                <div class="col-md-6">

                                    <h4 class="mb-0"><b style="color: rgb(46, 125, 50); font-size: 17px;">PAID HIGH SPEED INTERNET</b></h4><br>
                                    <h5 class="mb-0"><b style="color: rgb(46, 125, 50); font-size: 12px;">Recommended for all purpose including streaming, can be shared across 4 devices.</b><br></h5>

                                </div>

                            </div>


                            <div class="row">

                                <div class="col-md-6">
                                    <button class="button">Submit</button>
                                </div>

                                <div class="col-md-6">
                                    <button class="button">Click Here</button>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-6">
                                    <p style="color: rgb(46, 125, 50); font-size: 12px;"><b style="color: rgb(46, 125, 50); font-size: 12px;">Note:</b>Your special room rate is inclusive of Wi-Fi for 1 device only.</p>

                                    <p style="color: rgb(46, 125, 50); font-size: 12px;">The speed provided is sufficient for email and basic surfing.</p>
                                    <p style="color: rgb(46, 125, 50); font-size: 12px;">High speed multi-device Wi-Fi is available only on chargeable basis.</p>

                                </div>
                            </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

<script>
    var mfactors_pending = false;
</script>

<script src="/app-assets/vendors/js/vendors.min.js"></script>
<script src="/app-assets/js/core/app-menu.js"></script>
<script src="/app-assets/js/core/app.js"></script>
<script src="/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>

<script src="javascripts/index.js"></script>

</body>

</html>