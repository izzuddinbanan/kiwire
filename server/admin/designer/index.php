<?php

$kiw['module'] = "Login Engine -> Desiger Tool -> List";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;


require_once dirname(__FILE__, 2) . '/includes/include_config.php';
require_once dirname(__FILE__, 2) . '/includes/include_session.php';
require_once dirname(__FILE__, 2) . '/includes/include_general.php';
require_once dirname(__FILE__, 2) . '/includes/include_connection.php';

$kiw_page_unique = "";

if (isset($_GET['page']) && !empty($_GET['page'])) {

    if (strlen($_GET['page']) == 8) {


        $kiw_page = $kiw_db->escape($_GET['page']);

        $kiw_page = $kiw_db->query_first("SELECT * FROM kiwire_login_pages WHERE unique_id = '{$kiw_page}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


        if (empty($kiw_page)) {

            header("Location: /admin/login_engine_page_designer.php");

            die();

        } else {

            $kiw_page_unique = $kiw_page['unique_id'];

        }


    } else {

        header("Location: /admin/login_engine_page_designer.php");

        die();

    }


} else {


    $kiwire_existed['kcount'] = 1;


    while ($kiwire_existed['kcount'] > 0) {


        $kiw_page_unique = hash("sha256", time() + $_SERVER['name']);

        $kiw_page_unique = substr($kiw_page_unique, 2, 8);


        $kiwire_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_login_pages WHERE unique_id = '{$kiw_page_unique}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


    }


}


foreach (array("bg_lg", "bg_md", "bg_sm") as $kiw_bg_select){

    if (!empty($kiw_page[$kiw_bg_select])){

        $kiw_background = $kiw_page[$kiw_bg_select];

        break;

    }

}


?>

<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

    <title><?= sync_brand_decrypt(SYNC_PRODUCT) . " " . sync_brand_decrypt(SYNC_TITLE) ?><?= isset($kiw['page']) ? " | {$kiw['page']}" : '' ?></title>

    <link rel="apple-touch-icon" href="/app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">

    <link href="/libs/google-fonts/montserrat/montserrat.css" rel="stylesheet">

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

    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/forms/select/select2.min.css">

    <link rel="stylesheet" type="text/css" href="/admin/designer/contentbuilder/contentbuilder.css">
    <link rel="stylesheet" type="text/css" href="/admin/designer/assets/minimalist-blocks/content.css">

    <link rel="stylesheet" type="text/css" href="/admin/designer/assets/scripts/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="/admin/designer/assets/scripts/slick/slick-theme.css" />

    <style>

        .snippet-helper {

            background-color: white;
            padding: 2px;
            font-size: smaller;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            text-transform: uppercase;
            border-top: lightgrey thin solid;

        }

    </style>


</head>


<?php

require_once dirname(__FILE__, 2) . '/includes/include_access.php';
require_once dirname(__FILE__, 2) . "/includes/include_connection.php";

$kiw_db = Database::obtain();


if (!empty($kiw_background)){

    ?>
    <body style="background: url(<?= $kiw_background ?>); <?= $kiw_page['bg_css'] ?>" class="vertical-layout vertical-menu-modern 1-column  navbar-sticky footer-static" data-open="click" data-menu="vertical-menu-modern" data-col="1-column">
    <?php

} else {

    ?>
    <body class="vertical-layout vertical-menu-modern 1-column  navbar-sticky footer-static" data-open="click" data-menu="vertical-menu-modern" data-col="1-column">
    <?php

}

?>

<div class="app-content content">

    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>

    <nav class="header-navbar navbar-expand-lg navbar navbar-light navbar-shadow">
        <div class="navbar-wrapper">
            <div class="navbar-container content">
                <div class="navbar-collapse" id="navbar-mobile">

                    <div class="mr-auto float-left bookmark-wrapper d-flex align-items-center">

                        &nbsp;
                        &nbsp;
                        <button type="button" class="btn btn-dark btn-sm k-save">Save</button>
                        &nbsp;
                        &nbsp;
                        <button type="button" class="btn btn-dark btn-sm k-cancel">Cancel</button>

                    </div>

                    <ul class="nav navbar-nav float-right">

                        <li class="nav-item d-none d-lg-block"><a class="nav-link nav-link-expand"><i
                                        class="ficon feather icon-maximize"></i></a></li>

                        <li class="dropdown dropdown-user nav-item">
                            <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
                                <div class="user-nav d-sm-flex d-none"><span class="user-name text-uppercase text-bold-600"><?= $_SESSION['full_name'] ?></span>
                                    <span class="user-status"><?= $_SESSION['tenant_id'] ?></span>
                                </div>
                                <span>
                                    <span class="avatar">
                                        <span class="avatar-content"><span class="avatar-icon feather icon-user"></span></span>
                                    </span>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="/admin/index.php?logout=now"><i class="feather icon-power"></i> Logout</a>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </nav>



    <div class="content-wrapper" style="margin-top: auto;">

        <div class="content-body">

            <div class="row">

                <div class="col-12">
                    <div id="cb-area" style="padding: 10px;">
                        <div class="cb-container" style="margin: auto;">

                            <?php if (empty($kiw_page)) { ?>

                                [ Please wait. Loading our designing tools.. ]

                            <?php } else { ?>

                                <?= urldecode(base64_decode($kiw_page['content'])) ?>

                            <?php } ?>

                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>


<div class="modal fade text-left" id="save-page-modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33">Page information</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <div class="modal-body">

                    <label>Page Name</label>
                    <div class="form-group">
                        <input type="text" name="page_name" id="page_name" value="<?= (isset($kiw_page['page_name']) ? $kiw_page['page_name'] : "") ?>" class="page_name form-control required"/>
                    </div>

                    <label>Page Unique ID</label>
                    <div class="form-group">
                        <input type="text" name="page_id" id="page_id" value="<?= $kiw_page_unique ?>" disabled class="page_id form-control"/>
                    </div>

                    <label>Remark</label>
                    <div class="form-group">
                        <input type="text" name="remark" id="remark" value="<?= (isset($kiw_page['remark']) ? $kiw_page['remark'] : "") ?>" class="remark form-control"/>
                    </div>

                    <label>Page Type (Optional)</label>
                    <div class="form-group">
                        <select name="function" id="function" class="function select2 form-control" data-style="btn-default">
                            <option value="landing" <?= (($kiw_page['purpose'] == "landing") ? "selected" : "") ?>>
                                Landing Page
                            </option>
                            <option value="landingwinfo" <?= (($kiw_page['purpose'] == "landingwinfo") ? "selected" : "") ?>>
                                Landing Page + Last Account Info
                            </option>
                            <option value="campaign" <?= (($kiw_page['purpose'] == "campaign") ? "selected" : "") ?>>
                                Campaign Page
                            </option>
                            <option value="qr" <?= (($kiw_page['purpose'] == "qr") ? "selected" : "") ?>>
                                QR Login
                            </option>
                            <option value="survey" <?= (($kiw_page['purpose'] == "survey") ? "selected" : "") ?>>
                                Survey Page
                            </option>
                            <option value="status" <?= (($kiw_page['purpose'] == "status") ? "selected" : "") ?>>
                                Account Info [ Status ] Page
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="vs-checkbox-con vs-checkbox-primary">
                            <input type="checkbox" value="false" class="default_page" <?= (($kiw_page['default_page'] == "y") ? "checked" : "") ?>>
                            <span class="vs-checkbox">
                                <span class="vs-checkbox--check">
                                    <i class="vs-icon feather icon-check"></i>
                                </span>
                            </span>
                            <span class="">Set this page as default</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="vs-checkbox-con vs-checkbox-primary">
                            <input type="checkbox" value="false" class="count_impress" <?= (($kiw_page['count_impress'] != "n") ? "checked" : "") ?>>
                            <span class="vs-checkbox">
                                <span class="vs-checkbox--check">
                                    <i class="vs-icon feather icon-check"></i>
                                </span>
                            </span>
                            <span class="">Count this page impression</span>
                        </div>
                    </div>

                    <input type="hidden" name="bg_sm" class="bg_sm" value="">
                    <input type="hidden" name="bg_md" class="bg_md" value="">
                    <input type="hidden" name="bg_lg" class="bg_lg" value="">
                    <input type="hidden" name="bg_css" class="bg_css" value="">

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-warning round waves-effect waves-light cancel-button" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create">Save</button>

                </div>
                <input type="hidden" id="token" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
            </form>
        </div>
    </div>
</div>



<div class="modal fade text-left" id="survey-id" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Survey</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>


            <div class="modal-body">

                <div class="col-12">
                    <label class="label" for="survey-id-val">Please select a survey</label>
                    <select id="survey-id-val" class="select2 form-control">
                        <option value="none">None</option>

                        <?php

                        $kiw_surveys = $kiw_db->fetch_array("SELECT SQL_CACHE id,name FROM kiwire_survey_list WHERE tenant_id = '{$_SESSION['tenant_id']}' AND status = 'y'");

                        foreach($kiw_surveys as $kiw_survey){

                            echo "<option value='{$kiw_survey['id']}'>{$kiw_survey['name']}</option>\n\t\t\t\t";

                        }


                        ?>

                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary round waves-effect waves-light btn-survey-ok">OK</button>
            </div>

        </div>
    </div>
</div>


<footer class="footer footer-static footer-light">
    <p class="clearfix blue-grey lighten-2 mb-0 text-center"><span class="d-block d-md-inline-block mt-25"><?= sync_brand_decrypt(SYNC_COPYRIGHT) ?></span></p>
</footer>


<div class="sidenav-overlay"></div>
<div class="drag-target"></div>


<script src="/app-assets/vendors/js/vendors.min.js"></script>
<script src="/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="/app-assets/vendors/js/extensions/toastr.min.js"></script>
<script src="/app-assets/vendors/js/forms/validation/jquery.validate.min.js"></script>

<script src="/app-assets/vendors/js/forms/select/select2.min.js"></script>

<script src="/assets/js/parsley.js"></script>
<script src="/app-assets/js/core/app-menu.js"></script>
<script src="/app-assets/js/core/app.js"></script>
<script src="/app-assets/js/scripts/components.js"></script>
<script src="/app-assets/vendors/js/forms/spinner/jquery.bootstrap-touchspin.js"></script>

<script src="/admin/designer/contentbuilder/contentbuilder.min.js"></script>

<script src="/assets/js/jquery-ui.min.js" type="text/javascript"></script>
<script src="/assets/js/jquery.ui.touch-punch.min.js" type="text/javascript"></script>
<script src="/assets/js/html2canvas.js" type="text/javascript"></script>

<script src="/admin/designer/assets/minimalist-blocks/content.php" type="text/javascript"></script>
<script src="/admin/designer/contentbuilder/saveimages.js" type="text/javascript"></script>
<script src="/admin/designer/assets/scripts/slick/slick.min.js" type="text/javascript"></script>

<script>


    var current_page = "<?= $kiw_page_unique ?>";

    var cb_area = null;


    $(document).ready(function () {


        cb_area = $.contentbuilder({
            container: '.cb-container',
            snippetOpen: false,
            snippetCategories: [[100, "Pages"]],
            defaultSnippetCategory: 100,
            imageselect: '/admin/designer/images.php',
            moduleConfig: [{
                "moduleSaveImageHandler": "/admin/designer/contentbuilder/saveimage-module.php"
            }],
            htmlSyntaxHighlighting: true,
            buttons: ["backgroundchanger", "bold", "italic", "createLink", "align", "color", "formatPara", "font", "formatting", "list", "textsettings", "image", "tags", "removeFormat"],
            buttonsMore: ["icon", "html", "preferences"],
            row: 'row',
            cols: ['col-md-1', 'col-md-2', 'col-md-3', 'col-md-4', 'col-md-5', 'col-md-6', 'col-md-7', 'col-md-8', 'col-md-9', 'col-md-10', 'col-md-11', 'col-md-12'],
            onAdd: function (content) {

                if (content.indexOf("survey") > 0){

                    $("#survey-id").modal();

                }

                return content;

            }
        });


        $(".ui-draggable").each(function (){

            $(this).append("<div class='snippet-helper'>" + switch_snippet_name($(this).data("id")) + "</div>")

        });


        $(".k-save").off().on("click", function () {

            $("#save-page-modal").modal();

        });


        $(".k-cancel").off().on("click", function(){

            window.location.href = '/admin/login_engine_page_designer.php';

        });


        $(".btn-create").off().on("click", function () {


            $(".k-cancel").css("display", "none");
            $(".k-save").css("display", "none");

            if ($(".page_name").val().length === 0){

                swal({
                    title: "Missing Page Name",
                    text: "Please provide a name for this page.",
                    icon: "error"
                });

                return;

            }


            swal({
                title: "Please wait..",
                text: "We are saving your page.",
                showCancelButton: false,
                showConfirmButton: false
            });


            $('.modal').on('hidden.bs.modal', function (e) {

                real_generate_page();

            });


            $(".modal").modal("hide");


        });


        $(".btn-survey-ok").off().on("click", function () {


            get_survey_data();


        });


    });


    function get_survey_data(){


        let survey_id = $("#survey-id-val").val();

        if (survey_id !== "none") {


            $.ajax({
                url: "/admin/designer/survey.php",
                method: "post",
                data: {
                    survey_name: survey_id
                },
                success: function (response) {

                    if (response['status'] === "success") {


                        // set the title and description

                        $(".survey-title > h4").html(response['data']['name']);
                        $(".survey-description").html(response['data']['description']);


                        // set the questions and answer

                        if (response['data']['questions'] !== undefined && response['data']['questions'] !== null) {


                            let question_str = '', question_count = 1, choices = null, choice_id = null;

                            let questions = $.parseJSON(atob(response['data']['questions']));


                            for (let kindex in questions){


                                if (questions[kindex]['type'] === "text-single") {


                                    question_str += '<div class="col-12 mb-2">';
                                    question_str += '<div class="mb-1"><h5>Question ' + question_count + '</h5></div>';
                                    question_str += '<div class="mb-1">' + questions[kindex]['question'] + '</div>';
                                    question_str += '<div class="mb-1"><input type="text" name="answer[' + question_count + ']" class="form-control"' + (questions[kindex]['required'] === "true" ? "required" : "") + '></div>';
                                    question_str += '</div>';


                                } else if (questions[kindex]['type'] === "select-single"){


                                    choices = $.parseJSON(questions[kindex]['choice']);
                                    
                                    
                                    question_str += '<div class="col-12 mb-2">';
                                    question_str += '<div class="mb-1"><h5>Question ' + question_count + '</h5></div>';
                                    question_str += '<div class="mb-1">' + questions[kindex]['question'] + '</div>';
                                    question_str += '<div class="mb-1">';

                                    for (kindexs in choices) {

                                        if (typeof choices[kindexs] != "function") {

                                            choice_id = make_id(8);

                                            question_str += '<fieldset>';
                                            question_str += '<div class="custom-control custom-radio">';
                                            question_str += '<input type="radio" id="' + choice_id + '" class="custom-control-input" name="answer[' + question_count + ']" value="' + choices[kindexs] + '"' + (questions[kindex]['required'] === "true" ? "required" : "") + '>';


                                            
                                            question_str += '<label for="' + choice_id + '" class="custom-control-label">' + choices[kindexs] + '</label>';
                                            question_str += '</div>';
                                            question_str += '</fieldset>';

                                            choice_id = null;

                                        }

                                    }

                                    choices = null;

                                    question_str += '</div>';
                                    question_str += '</div>';


                                } else if (questions[kindex]['type'] === "select-multi"){


                                    choices = $.parseJSON(questions[kindex]['choice']);

                                    question_str += '<div class="col-12 mb-2">';
                                    question_str += '<div class="mb-1"><h5>Question ' + question_count + '</h5></div>';
                                    question_str += '<div class="mb-1">' + questions[kindex]['question'] + '</div>';
                                    question_str += '<div class="mb-1">';


                                    for (kindexs in choices) {

                                        if (typeof choices[kindexs] != "function") {

                                            choice_id = make_id(8);

                                            question_str += '<fieldset>';
                                            question_str += '<div class="custom-control custom-checkbox">';
                                            question_str += '<input type="checkbox" id="' + choice_id + '" class="custom-control-input" name="answer[' + question_count + '][]" value="' + choices[kindexs] + '"' + (questions[kindex]['required'] === "true" ? "required" : "") + '>';                                            
                                            
                                            question_str += '<label for="' + choice_id + '" class="custom-control-label">' + choices[kindexs] + '</label>';
                                            question_str += '</div>';
                                            question_str += '</fieldset>';

                                            choice_id = null;

                                        }

                                    }

                                    choices = null;

                                    question_str += '</div>';
                                    question_str += '</div>';


                                }


                                question_count++;


                            }


                            question_str += '<input type="hidden" name="survey_id" value="' + response['data']['unique_id'] + '">';

                            $("form.survey > .form-body > .row:first").html(question_str);


                        }


                        $("#survey-id").modal("hide");


                    } else {


                        swal("Error", response['message'], "error");


                    }

                },
                error: function (response) {

                    swal("Error", "There is an error. Please try again.", "error");

                }
            });


        }


    }


    function real_generate_page() {

        html2canvas(document.querySelector(".content-wrapper"), {logging: false, width: '400px'}).then(canvas => {

            let page_thumbnail = canvas.toDataURL('image/png');

            $.ajax({
                type: 'post',
                url: "/admin/designer/thumbnail.php",
                data: {
                    name: $(".page_id").val(),
                    image: page_thumbnail
                },
                success: function () {

                    let cbcontainer = $(".cb-container");

                    cbcontainer.saveimages({
                        handler: '/admin/designer/contentbuilder/saveimage.php',
                        onComplete: function () {

                            let page_html = cb_area.html();
                            page_html = btoa(encodeURIComponent(page_html));

                            $.ajax({
                                url: "/admin/designer/save_page.php",
                                method: "post",
                                data: {
                                    "page_name": $(".page_name").val(),
                                    "page_unique_id": $(".page_id").val(),
                                    "page_purpose": $(".function").val(),
                                    "page_remark": $(".remark").val(),
                                    "page_default": $(".default_page").prop("checked"),
                                    "count_impress": $(".count_impress").prop("checked"),
                                    "bg_sm": $(".bg_sm").val(),
                                    "bg_md": $(".bg_md").val(),
                                    "bg_lg": $(".bg_lg").val(),
                                    "bg_css": $(".bg_css").val(),
                                    "token": $("#token").val(),
                                    "page_content": page_html
                                },
                                success: function (respond) {

                                    if (respond['status'] === "success") {

                                        swal("Success", "Page [ " + $(".page_name").val() + " ] has been saved.");

                                    } else {

                                        swal("Error", respond['message']);

                                    }

                                    $(".k-cancel").html("Back").css("display", "inline-block");
                                    $(".k-save").css("display", "inline-block");

                                },
                                error: function () {

                                    swal("Error", "Error occurred while we trying to save [ " + $(".page_name").val() + " ]. Please try again.");

                                    $(".k-cancel").css("display", "inline-block");
                                    $(".k-save").css("display", "inline-block");

                                }
                            });

                        }
                    });

                    cbcontainer.data('saveimages').save();

                },
                error: function () {

                    swal("Error", "Error occurred while we trying to save [ " + $(".page_name").val() + " ]. Please try again.");

                }

            });


        });


    }


    function switch_snippet_name(snippet_id){


        switch (snippet_id){
            case 1: return "Bpanel";
            case 2: return "Next Button";
            case 3: return "Date";
            case 4: return "Login with Registered Account";
            case 5: return "Voucher Login";
            case 6: return "External Radius Login";
            case 7: return "Microsoft Active Directory Login";
            case 8: return "LDAP Login";
            case 9: return "External Database Login";
            case 10: return "Campaign + Login";
            case 11: return "One Click Login";
            case 12: return "One Click + Data";
            case 13: return "Social Login";
            case 14: return "Register with Social Media";
            case 15: return "Qr Code Login";
            case 16: return "Terms and Condition";
            case 17: return "Public Signup";
            case 18: return "Sponsor Signup";
            case 19: return "Email Verification";
            case 20: return "SMS Signup";
            case 21: return "SMS Request OTP";
            case 22: return "Temporary Access";
            case 23: return "Pending Verify";
            case 24: return "Account Verified";
            case 25: return "SMS Register";
            case 26: return "OTP Login";
            case 27: return "Forgot Password";
            case 28: return "Change Password";
            case 29: return "Topup Code";
            case 30: return "Campaign 1";
            case 31: return "Campaign 2";
            case 32: return "Campaign 3";
            case 33: return "Survey";
            case 34: return "Rate";
            case 35: return "Image";
            case 36: return "Element Video";
            case 37: return "Spacer";
            case 38: return "Header";
            case 39: return "Button";
            case 40: return "Photo";
            case 41: return "Work Steps";
            case 42: return "FAQ 01";
            case 43: return "As Feature On";
            case 44: return "FAQ 1";
            case 45: return "FAQ 2";
            case 46: return "FAQ 3";
            case 47: return "FAQ 4";
            case 48: return "FAQ 5";
            case 49: return "FAQ 6";

            default: return "Unknown";

        }

    }

    // https://stackoverflow.com/questions/1349404/generate-random-string-characters-in-javascript
    function make_id(length) {

        var result           = '';
        var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;

        for ( var i = 0; i < length; i++ ) {

            result += characters.charAt(Math.floor(Math.random() * charactersLength));

        }

        return result;

    }


</script>

</body>

</html>