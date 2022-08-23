<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Diagnose Network Issue</title>

    <link rel="stylesheet" href="/app-assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/app-assets/fonts/font-awesome/css/font-awesome.css">

</head>
<body>

<div class="container">

    <div class="col">
        &nbsp;
    </div>

    <div class="row">

        <div class="col-sm-8 offset-sm-2">

            <div class="row text-center">
                <div class="col-sm-12">
                    <h2>Diagnose Network Wifi Issue</h2>
                    <p>Please click on the [Diagnose] button to start check.</p>
                </div>
            </div>

            <div class="row pt-sm-2 pb-sm-2">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr class="text-center">
                            <th>DESTINATION</th>
                            <th>RESULT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div>
                                    Social Gate
                                </div>
                                <div class="socialgate-result-space text-sm"></div>
                            </td>

                            <td class="text-center">
                                <h4 class="socialgate"><i class="fa fa-question-circle"></i></h4>
                            </td>
                        </tr>

                        <tr>
                            <td>Facebook</td>
                            <td class="text-center">
                                <h4 class="facebook"><i class="fa fa-question-circle"></i></h4>
                            </td>
                        </tr>

                        <tr>
                            <td>Twitter</td>
                            <td class="text-center">
                                <h4 class="twitter"><i class="fa fa-question-circle"></i></h4>
                            </td>
                        </tr>

                        <tr>
                            <td>Instagram</td>
                            <td class="text-center">
                                <h4 class="instagram"><i class="fa fa-question-circle"></i></h4>
                            </td>
                        </tr>

                        <tr>
                            <td>Zalo</td>
                            <td class="text-center">
                                <h4 class="zalo"><i class="fa fa-question-circle"></i></h4>
                            </td>
                        </tr>

                        <tr>
                            <td>Line</td>
                            <td class="text-center">
                                <h4 class="line"><i class="fa fa-question-circle"></i></h4>
                            </td>
                        </tr>

                        <tr>
                            <td>Kakaotalk</td>
                            <td class="text-center">
                                <h4 class="kakaotalk"><i class="fa fa-question-circle"></i></h4>
                            </td>
                        </tr>

                        <tr>
                            <td>Google</td>
                            <td class="text-center">
                                <h4 class="google"><i class="fa fa-question-circle"></i></h4>
                            </td>
                        </tr>

                        <tr>
                            <td>GStatic</td>
                            <td class="text-center">
                                <h4 class="gstatic"><i class="fa fa-question-circle"></i></h4>
                            </td>
                        </tr>

                        <tr>
                            <td>Apple</td>
                            <td class="text-center">
                                <h4 class="apple"><i class="fa fa-question-circle"></i></h4>
                            </td>
                        </tr>

                        <tr>
                            <td>Microsoft</td>
                            <td class="text-center">
                                <h4 class="microsoft"><i class="fa fa-question-circle"></i></h4>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <div class="col-sm-12 text-center">
                <button class="btn btn-dark btn-diagnose">Diagnose</button>
            </div>

        </div>

    </div>

    <div class="col">
        &nbsp;
    </div>

</div>

</body>

<script src="/app-assets/js/core/libraries/jquery.min.js"></script>
<script src="/app-assets/js/core/libraries/bootstrap.min.js"></script>

<script>

    var whitelist = [
        {url: "https://socialgate.synchroweb.com/", section: "socialgate"},
        {url: "https://facebook.com/", section: "facebook"},
        {url: "https://twitter.com/", section: "twitter"},
        {url: "https://www.instagram.com/", section: "instagram"},
        {url: "https://zalo.me/", section: "zalo"},
        {url: "https://line.me/", section: "line"},
        {url: "https://www.kakaocorp.com/", section: "kakaotalk"},
        {url: "https://www.google.com/", section: "google"},
        {url: "https://www.apple.com/", section: "apple"},
        {url: "https://www.gstatic.com/", section: "gstatic"},
        {url: "https://www.microsoft.com/", section: "microsoft"},
    ];


    $(document).ready(function () {

        $(".btn-diagnose").on("click", function() {

            $("h4").each(function () {

                $(this).html('<i class="fa fa-question-circle text-dark"></i>')

            });

            for (let i = 0; i < whitelist.length; i++){

                $("." + whitelist[i].section + "-result-space").html("");

                try_connect(whitelist[i].url, whitelist[i].section);

            }

        });

    });


    function try_connect(target_url, url_section){

        $.ajax({
            url: target_url,
            method: "HEAD",
            timeout: 3000,
            success: function (req, stat, err) {

                $("h4." + url_section).html("SUCCESS: " + JSON.stringify(err));

            },
            error: function (req, stat, err) {

                if (stat === "timeout") {

                    $("h4." + url_section).html('<i class="fa fa-times text-danger"></i>');

                    let domain = target_url.replace('http://','').replace('https://','').replace('www.','').split(/[/?#]/)[0];

                    let result_space = $("." + url_section + "-result-space");
                    result_space.html(result_space.html() + "<span style='font-size: smaller;'>[" + domain + "] : unreachable </span><br />");

                } else {

                    $("h4." + url_section).html("ERROR: " + JSON.stringify(err) + JSON.stringify(req));

                }

            }
        });

    }


</script>


</html>