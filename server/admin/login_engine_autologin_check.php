<?php

$kiw['module'] = "Login Engine -> Auto Login Checks";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_header.php';
require_once 'includes/include_nav.php';
require_once 'includes/include_access.php';
require_once "includes/include_connection.php";

$kiw_db = Database::obtain();

$kiw_available_list = file_get_contents(dirname(__FILE__, 2) . "/user/templates/kiwire-auto-login-checks.json");
$kiw_available_list = json_decode($kiw_available_list, true);

$kiw_selected_list = $kiw_db->query_first("SELECT check_arrangement_auto FROM kiwire_clouds WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");
$kiw_selected_list = explode(",", $kiw_selected_list['check_arrangement_auto']);


?>

<link rel="stylesheet" href="/app-assets/vendors/css/extensions/dragula.min.css">

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_engine_autologin_check_title">Auto-login Check Detection</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_engine_autologin_check_subtitle">
                                Set the arrangement of check to be execute to automatically login a device
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <section id="dd-with-handle">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card overflow-hidden">
                        <div class="card-content">
                            <div class="card-body">

                            <form class="create-form" action="#">

                                <input type="hidden" id="token" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

                                <div class="row mb-2">
                                    <div class="col-md-12">
                                        <span data-i18n="login_engine_autologin_check_select">Please select check that you want to perform when user connected to your network and their order.</span>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <h4 class="my-1" data-i18n="login_engine_autologin_check_available">AVAILABLE CHECK ITEMS</h4>
                                        <ul class="list-group list-group-flush" id="multiple-list-group-a" style="min-height: 50px;">

                                            <?php foreach ($kiw_available_list as $kiw_available){ ?>

                                                <?php if (!in_array($kiw_available['function'], $kiw_selected_list)){ ?>

                                                    <li class="list-group-item" data-function="<?= $kiw_available['function'] ?>">
                                                        <div class="media">
                                                            <span class="mr-2"><i class="fa fa-arrow-right"></i></span>
                                                            <div class="media-body">
                                                                <h5 class="mt-0"><?= $kiw_available['name'] ?></h5>
                                                                <?= $kiw_available['description'] ?>
                                                            </div>
                                                        </div>
                                                    </li>

                                                <?php } ?>

                                            <?php } ?>

                                        </ul>
                                    </div>
                                    <div class="col-md-6 col-sm-12 border">
                                        <h4 class="my-1" data-i18n="login_engine_autologin_check_order">CHECK TO BE PERFORM IN ORDER</h4>
                                        <ul class="list-group list-group-flush" id="multiple-list-group-b"  style="min-height: 50px;">

                                            <?php foreach ($kiw_selected_list as $kiw_selected){ ?>

                                                <?php foreach ($kiw_available_list as $kiw_available){ ?>

                                                    <?php if ($kiw_available['function'] == $kiw_selected){ ?>

                                                        <li class="list-group-item" data-function="<?= $kiw_available['function'] ?>">
                                                            <div class="media">
                                                                <span class="mr-2"><i class="fa fa-arrow-right"></i></span>
                                                                <div class="media-body">
                                                                    <h5 class="mt-0"><?= $kiw_available['name'] ?></h5>
                                                                    <?= $kiw_available['description'] ?>
                                                                </div>
                                                            </div>
                                                        </li>

                                                    <?php } ?>

                                                <?php } ?>

                                            <?php } ?>
                                         
                                        </ul>
                                    </div>
                                </div>
                            </form>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-primary btn-save-data" data-i18n="login_engine_autologin_check_save">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>


</div>

<?php

require_once "includes/include_footer.php";

?>

<script src="/app-assets/vendors/js/extensions/dragula.min.js"></script>

<script>

    $(document).ready(function () {

        dragula([document.getElementById('multiple-list-group-a'), document.getElementById('multiple-list-group-b')]);

    });


    $(".btn-save-data").on("click", function () {

        let selected_functions = [];

        $("#multiple-list-group-b").find("li").each(function(){

            selected_functions.push($(this).data("function"));

        });


        $.ajax({
            "url": "ajax/ajax_login_engine_autologin_check.php",
            "method": "post",
            "data": {
                "functions_list" : selected_functions.join(","),
                "token": $('#token').val()
            },
            "success": function (response) {

                toastr.success("Your setting has been saved.");

            },
            "error": function (response) {

                toastr.success("There is something wrong happened. Please try again.");

            }
        });

    });


</script>
