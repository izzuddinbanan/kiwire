
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
                        <h4 class="mb-0"><b style="color: rgb(46, 125, 50);">Authentication</b><br></h4>
                        <p><b style="color: rgb(46, 125, 50); font-size: 12px;">For in-room access, please enter your room number name and last name below:</b></p>
                        <form method="post" action="/custom/securelynkx/pms/login/index.php">

                            <table align="center">
                                <tbody>
                                    <tr>
                                        <td style="color: rgb(46, 125, 50); font-size: 12px;">Room No:
                                            <input type="text" size="6" name="username" id="username" placeholder="">
                                        </td>
                                        <td style="color: rgb(46, 125, 50); font-size: 12px;"> Last Name:
                                            <input type="text" size="6" name="username" id="username" placeholder="">
                                        </td>
                                    </tr>
                                </tbody>
                            </table><br>

                            <p><b style="color: rgb(46, 125, 50); font-size: 12px;">Please read and accept terms and conditions</b></p>

                            <table align="center">
                                <tbody>
                                    <tr>
                                        <td>
                                            <span style="color:#0099de;"><a class="next-page-btn" data-next-page="8af4a9a9" style="text-align: left; font-size: 15px;">Internet FAQs&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</a></span>
                                        </td>
                                        <td> <input type="checkbox" class="custom-control-input" id="tnc" name="tnc" required="" value=""><label for="tnc" class="custom-control-label" style="color: rgb(21, 101, 192);"><b style="color: rgb(46, 125, 50); font-size: 12px;">&nbsp;I Agree*</b></label><b>
                                            </b>
                                        </td>
                                        <td style="color: rgb(46, 125, 50); font-size: 12px;">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Coupon users, please <span style="color:#0099de;"><a class="next-page-btn" data-next-page="8af4a9a9">click here </a></span>for login</td>
                                    </tr>
                                </tbody>
                            </table>

                            <button type="submit" style="border:1px solid green; background-color: transparent;" align="center">Submit</button>

                        </form>
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