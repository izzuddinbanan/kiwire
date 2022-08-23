<?php

$kiw['module'] = "Report -> Survey -> Response Data";
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
require_once "includes/include_report.php";


$kiw_db = Database::obtain();


$kiw_surveys = $kiw_db->fetch_array("SELECT * FROM kiwire_survey_list WHERE tenant_id = '{$_SESSION['tenant_id']}'");


if (isset($_REQUEST['survey_id']) && !empty($_REQUEST['survey_id'])) {


    $kiw_survey_id = $_REQUEST['survey_id'];


    $kiw_questions = $kiw_db->query_first("SELECT questions FROM kiwire_survey_list WHERE id = '{$kiw_survey_id}' LIMIT 1");

    $kiw_questions = json_decode(base64_decode($kiw_questions['questions']), true);
}


$kiw_start = $_REQUEST['startdate'];

$kiw_end = $_REQUEST['enddate'];


?>

<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="camp_survey_title">Survey Response Data</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="camp_survey_subtitle">
                                List of all survey response data
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="row">
        <div class="col-12 mb-1">
            <button id="filter-btn" class="float-right btn btn-icon btn-primary btn-xs fa fa-filter"></button>
        </div>
    </div> -->

    <div class="content-body">

        <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="col-12">
                            <div class="form-group row">
                                <h6 class="text-bold-500" data-i18n="report_account_expiry_search">Filter :</h6>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                    <label for="startdate" data-i18n="data_from">Date From</label>
                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<?php echo empty($kiw_start) ? report_date_view(report_date_start("", 2)) : $kiw_start; ?>'>
                                </div>

                            </div>

                            <div class="col-md-4">
                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                    <label for="enddate" data-i18n="data_until">Date Until</label>
                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<?php echo empty($kiw_end) ? report_date_view(report_date_start("", 1)) : $kiw_end; ?>'>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="zone" data-i18n="camp_survey_modal_survey">Survey Name</label>
                                    <select class="select2 form-control" name="survey_id" id="">

                                        <? if (count($kiw_surveys) > 0) { ?>

                                            <? foreach ($kiw_surveys as $kiw_survey) { ?>

                                                <option value="<?= $kiw_survey['id'] ?>" <?= ($kiw_survey['id'] == $kiw_survey_id ? "selected" : "") ?>>Survey: <?= $kiw_survey['name'] ?></option>

                                            <? } ?>

                                    </select>

                                <? } else { ?>

                                    <option value="" data-i18n="camp_survey_no_survey_created">No survey has been created</option>

                                <? } ?>
                                </div>
                            </div>

                        </div>
                        <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="bandwidth_account_usage_search">Search</button>

                    </div>
                </div>
            </div>
        </section>

        <section id="report_table" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="camp_survey_no">No</th>
                                        <th data-i18n="camp_survey_date">Date</th>
                                        <th data-i18n="camp_survey_username">Username</th>
                                        <th data-i18n="camp_survey_mac_address">MAC Address</th>

                                        <?php for ($kiw_x = 1; $kiw_x <= count($kiw_questions); $kiw_x++) { ?>

                                            <th>Question <?= $kiw_x ?></th>

                                        <?php } ?>

                                    </tr>
                                </thead>

                                <?php if (count($kiw_questions) > 0) { ?>

                                    <tbody class="campaign-data">
                                        <tr>
                                            <td colspan="<?= count($kiw_questions) + 4 ?>" class="text-center">Please wait..</td>
                                        </tr>
                                    </tbody>

                                <?php } else { ?>

                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="text-center" data-i18n="camp_survey_view_response">Please select a survey to view response</td>
                                        </tr>
                                    </tbody>

                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- <div class="modal fade text-left" id="filter_modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="mythememodal">Filter</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-content">
                            <div class="card-body">

                                <form class="form form-vertical">
                                    <div class="form-body">
                                        <div class="row">

                                            <div class="col-12">

                                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                                    <label for="startdate" data-i18n="camp_survey_modal_date_from">Date From</label>
                                                    <input type="text" class="form-control format-picker" name="startdate" id="startdate" value='<!?= report_date_view(report_date_start("", 2)) ?>'>
                                                </div>

                                                <div class="form-group" style="position:relative; left:auto; display:block;">
                                                    <label for="enddate" data-i18n="camp_survey_modal_date_until">Date Until</label>
                                                    <input type="text" class="form-control format-picker" name="enddate" id="enddate" value='<!?= report_date_view(report_date_start("", 1)) ?>'>
                                                </div>

                                                <div class="form-group">
                                                    <label for="zone" data-i18n="camp_survey_modal_survey">Survey Name</label>
                                                    <select class="form-control" name="survey_id" id="">

                                                        <!? if (count($kiw_surveys) > 0) { ?>

                                                            <!? foreach ($kiw_surveys as $kiw_survey) { ?>

                                                                <option value="<!?= $kiw_survey['id'] ?>" <!?= ($kiw_survey['id'] == $kiw_survey_id ? "selected" : "") ?>>Survey: <!?= $kiw_survey['name'] ?></option>

                                                            <!? } ?>

                                                    </select>

                                                <!? } else { ?>

                                                    <option value="" data-i18n="camp_survey_no_survey_created">No survey has been created</option>

                                                <!? } ?>
                                                </div>

                                            </div>

                                            <div class="col-12">
                                                <button type="button" id="filter-data" class="btn btn-primary round mr-1 mb-1 waves-effect waves-light pull-right" data-i18n="camp_survey_modal_filter">Filter</button>
                                                <button type="reset" class="btn btn-danger round mr-1 mb-1 waves-effect waves-light pull-right" data-dismiss="modal" data-i18n="camp_survey_modal_cancel">Cancel</button>
                                            </div>

                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div> -->

<script>
    var survey_id = '<?= $kiw_survey_id ?>';
</script>

<?php

require_once "includes/include_footer.php";
require_once "includes/include_report_footer.php";
require_once "includes/include_datatable.php";

?>