<?php

$kiw['module'] = "Account -> Auto Reset";
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

?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Auto Reset Profile</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Auto reset profile
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">

                        <button type="button" class="btn btn-primary waves-effect waves-light pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="button_add_auto_reset">Add Auto Reset</button>


                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="table_header_no">No</th>
                                        <th data-i18n="table_header_schedule">Schedule</th>
                                        <th data-i18n="table_header_profile">Profile</th>
                                        <!--th data-i18n="table_header_cooling_time">Cooling Time (Minutes)</th-->
                                        <th data-i18n="table_header_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th data-i18n="table_body_loading">
                                        Loading...
                                    </th>
                                </tbody>
                            </table>
                        </div>

                        <div class="modal fade text-left" id="inlineForm" role="dialog">

                            <div class="modal-dialog modal-dialog-centered" role="document">

                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_header_add_auto_reset">Add Auto Reset</h4>

                                        <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <form class="create-form" action="#">

                                        <div class="modal-body">

                                            <label data-i18n="modal_body_schedule">Schedule: </label>
                                            <div class="form-group">
                                                <select name="exec_when" id="exec_when" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                    <option value="t" data-i18n="modal_body_option_30_mins"> 30 Minutes</option>
                                                    <option value="h" data-i18n="modal_body_option_hourly"> Hourly</option>
                                                    <option value="d" data-i18n="modal_body_option_daily"> Daily</option>
                                                    <option value="w" data-i18n="modal_body_option_weekly"> Weekly</option>
                                                    <option value="m" data-i18n="modal_body_option_monthly"> Monthly</option>
                                                    <option value="y" data-i18n="modal_body_option_yearly"> Yearly</option>
                                                    <option value="ot" data-i18n="modal_body_option_limit"> Once Reached Limit</option>
                                                <?php
                                                    // include custom daily option if custom file exist
                                                    $kiw_custom = dirname(__FILE__, 2) . "/custom/{$_SESSION['tenant_id']}/schedule/schedule-daily.php";

                                                    if (file_exists($kiw_custom) == true) { ?>

                                                        <option value="cd" data-i18n="modal_body_option_custom_daily"> Custom Daily</option>
                                                       
                                                <? } ?>

                                                </select>
                                            </div>

                                            <label data-i18n="modal_body_profile">Profile: </label>
                                            <div class="form-group">
                                                <select name="profile" id="profile" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                    <?php

                                                    $kiw_row = $kiw_db->fetch_array("SELECT name FROM kiwire_profiles WHERE tenant_id = '$tenant_id' GROUP BY name");

                                                    foreach ($kiw_row as $record) {
                                                        echo "<option value=\"" . $record['name'] . "\"> " . $record['name'] . " </option> \n";
                                                    }

                                                    ?>
                                                </select>
                                            </div>

                                            <div class="form-group" style="display: none;" id="grace_space">
                                                <label data-i18n="modal_body_cooling_time">Cooling Time in Minutes: </label>
                                                <input type="text" id="grace" name="grace" class="form-control" value="0">
                                            </div>

                                            <div class="modal-footer">

                                                <input type="hidden" id="reference" name="reference" value="">
                                                <button type="button" class="btn btn-danger round waves-effect waves-light btn-cancel" data-dismiss="modal" data-i18n="modal_button_cancel">Cancel</button>
                                                <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_button_create">Create</button>

                                            </div>
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                    </form>
                                </div>
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
require_once "includes/include_datatable.php";

?>