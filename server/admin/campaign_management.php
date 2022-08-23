<?php

$kiw['module'] = "Campaign -> Campaign Management";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Campaigns Management</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Manage campaign with approval
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

                        <button type="button" class="btn btn-primary waves-effect waves-light pull-right mb-25" data-toggle="modal" data-target="#inlineForm" data-i18n="button_add_campaign">Add Campaign</button>

                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                <tr class="text-uppercase">
                                    <th data-i18n="table1_th_no">No</th>
                                    <th data-i18n="table1_th_name">Name</th>
                                    <th data-i18n="table1_th_status">Status</th>
                                    <th data-i18n="table1_th_start">Start</th>
                                    <th data-i18n="table1_th_end">End</th>
                                    <th data-i18n="table1_th_target">Target</th>
                                    <th data-i18n="table1_th_interval">Interval</th>
                                    <th data-i18n="table1_th_trigger">Trigger</th>
                                    <th data-i18n="table1_th_perform">Perform</th>
                                    <th data-i18n="table1_th_sort">Sort</th>
                                    <th data-i18n="table1_th_action">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <td colspan="8" data-i18n="table1_td_loading">Loading...</td>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" data-i18n="modal_1_title">Add or Edit Campaign</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>


            <div class="modal-body">

                <form class="create-form" action="#">

                    <div class="alert alert-primary mb-2" role="alert" data-i18n="modal_1_campaign">
                        Campaign
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label data-i18n="modal_1_campaign_label_name">Campaign Name: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="name" id="name" class="form-control" placeholder="eg: Merdeka Campaign" required>
                            </div>
                        </div>
                            
                        <div class="col-md-6">
                            <label data-i18n="modal_1_campaign_label_sort">Campaign Sort Order: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="c_order" id="c_order" class="form-control" placeholder="eg: 0" required>
                                <span style="font-size: smaller; padding: 10px;" data-i18n="modal_1_campaign_label_sort_span">* 0 for random order</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_campaign_label_remarks">Remarks: </label>
                            <div class="form-group">
                                <input type="text" name="remark" id="remark" placeholder="eg: description" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-primary mb-2" role="alert" data-i18n="modal_1_expire">
                        Expire
                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group" style="position:relative; left:auto; display:block;">
                                <label for="modal_1_expire_label_start" data-i18n="modal_1_expire_label_start">Start Date: </label>
                                <input type="text" class="form-control format-picker" name="date_start" id="date_start" placeholder="MM-DD-YYYY" value=''>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group" style="position:relative; left:auto; display:block;">
                                <label for="modal_1_expire_label_end" data-i18n="modal_1_expire_label_end">End Date: </label>
                                <input type="text" class="form-control format-picker" name="date_end" id="date_end" placeholder="MM-DD-YYYY" value=''>
                            </div>    
                        </div>    
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_expire_label_max_click">Maximum Clicks: </label>
                            <div class="form-group">
                                <input type="text" name="expire_click" id="expire_click" class="form-control" placeholder="eg: 0">
                                <label class="label" style="font-size: smaller; padding: 10px;" data-i18n="modal_1_expire_label_max_click_label">Blank or 0 for unlimited click</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_expire_label_max_imp">Maximum Impressions: </label>
                            <div class="form-group">
                                <input type="text" name="expire_impression" id="expire_impression" class="form-control" placeholder="eg: 0">
                                <label class="label" style="font-size: smaller; padding: 10px;" data-i18n="modal_1_expire_label_max_imp_label">Blank or 0 for unlimited impression</label>
                            </div>
                        </div>

                    </div>


                    <div class="alert alert-primary mb-2" role="alert" data-i18n="modal_1_who">
                        Who (Audience)
                    </div>

                    <div class="col-md-12 mb-1">

                        <div class="row mb-1">
                            <div class="col-12 pl-0" data-i18n="modal_1_who_target">
                                Target
                            </div>
                        </div>

                        <div class="col-2 d-inline-flex pl-0">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="target" id="target_all" value="all" checked>
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_who_target_all">All</label>
                            </div>
                        </div>

                        <div class="col-3 d-inline-flex">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="target" id="target_persona" value="persona">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_who_target_persona">Specific Persona</label>
                            </div>
                        </div>

                        <div class="col-3 d-inline-flex">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="target" id="target_zone" value="zone">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_who_target_zone">Specific Zone</label>
                            </div>
                        </div>

                    </div>

                    <div class="form-group space-persona">
                        <label data-i18n="modal_1_who_target_persona_label">Persona: </label>
                        <select name="target_value_persona" class="select2 form-control">
                            <option value="" data-i18n="modal_1_who_target_persona_label_none">None</option>
                            <?php

                            $kiw_temp = "SELECT id,name FROM kiwire_persona WHERE tenant_id = '{$tenant_id}'";
                            $kiw_temp = $kiw_db->fetch_array($kiw_temp);

                            foreach ($kiw_temp as $record) {
                                echo '<option value ="' . $record['name'] . '">' . $record['name'] . '</option>';
                            }

                            ?>
                        </select>
                    </div>

                    <div class="form-group space-zone">
                        <label data-i18n="modal_1_who_target_zone_label">Zone: </label>
                        <select name="target_value_zone" class="select2 form-control" data-style="btn-default">
                            <option value="all"  data-i18n="modal_1_who_target_persona_label_all">All Zone</option>
                            <?php

                            $kiw_temp = "SELECT * FROM kiwire_zone WHERE tenant_id = '{$tenant_id}'";
                            $kiw_temp = $kiw_db->fetch_array($kiw_temp);

                            foreach ($kiw_temp as $record) {

                                echo '<option value ="' . $record['name'] . '">' . $record['name'] . '</option>';

                            }

                            ?>
                            <option value="custom"  data-i18n="modal_1_who_target_persona_label_define">User Define Zone</option>
                        </select>
                    </div>

                    <div class="form-group space-custom-zone">
                        <label data-i18n="modal_1_who_target_persona_user_define">User Define Zone: </label>
                        <input name="c_zone" id="c_zone" class="form-control "/>
                    </div>

                    <div class="alert alert-primary mb-2" role="alert" data-i18n="modal_1_when">
                        When
                    </div>


                    <div class="col-md-12 mb-1">

                        <div class="row mb-1">
                            <div class="col-12 pl-0">
                                <label class="label" data-i18n="modal_1_when_interval">Interval</label>
                            </div>
                        </div>

                        <div class="col-2 d-inline-flex pl-0">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="c_interval" id="c_interval_always" value="always"
                                       checked>
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_when_interval_always">Always</label>
                            </div>
                        </div>

                        <div class="col-3 d-inline-flex">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="c_interval" id="c_interval_timeframe" value="timeframe">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_when_interval_specific">Specific Time Frame</label>
                            </div>
                        </div>

                    </div>


                    <div class="row space-time-frame">
                        <div class="col-12">
                            <div class="col-2 d-inline-flex pl-0">
                                <label class="label" data-i18n="modal_1_when_interval_specific_timeframe">Time Frame</label>
                            </div>
                            <div class="col-4 d-inline-flex">
                                <label class="mr-1" for="shour" data-i18n="modal_1_when_interval_specific_from">From</label>
                                <input type="text" name="shour" id="shour" class="form-control"/>
                            </div>
                            <div class="col-4 d-inline-flex">
                                <label class="mr-1" for="thour" data-i18n="modal_1_when_interval_specific_to">To</label>
                                <input type="text" name="thour" id="thour" class="form-control">
                            </div>
                        </div>
                        <dic class="col-12">
                            <div class="col-7 offset-3">
                                <label class="label" data-i18n="modal_1_when_interval_specific_label">These value format are Hour:Minute ( example 00:00 for 12 midnight )</label>
                            </div>
                        </dic>
                    </div>


                    <div class="col-md-12 mb-1">

                        <div class="row mb-1">
                            <div class="col-12 pl-0">
                                <label class="label" data-i18n="modal_1_when_trigger">Trigger</label>
                            </div>
                        </div>

                        <div class="col-2 d-inline-flex pl-0">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="c_trigger" value="connect">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_when_trigger_conn">Connect</label>
                            </div>
                        </div>

                        <div class="col-2 d-inline-flex">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="c_trigger" value="login">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_when_trigger_login">Login</label>
                            </div>
                        </div>

                        <div class="col-2 d-inline-flex">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="c_trigger" value="1stlogin">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_when_trigger_1stlogin">1st Login</label>
                            </div>
                        </div>

                        <div class="col-2 d-inline-flex">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="c_trigger" value="disconnect">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_when_trigger_disconn">Disconnect</label>
                            </div>
                        </div>

                    </div>


                    <div class="col-md-12 mb-1">

                        <div class="col-2 d-inline-flex pl-0">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="c_trigger" value="dwell">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_when_trigger_dwell">Dwell</label>
                            </div>
                        </div>

                        <div class="col-2 d-inline-flex">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="c_trigger" value="recurring">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_when_trigger_recurring">Recurring</label>
                            </div>
                        </div>

                        <div class="col-2 d-inline-flex">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="c_trigger" value="milestone">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_when_trigger_milestone">Milestone</label>
                            </div>
                        </div>

                        <div class="col-2 d-inline-flex">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="c_trigger" value="lastvisit">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_when_trigger_lastvisit">Last Visit</label>
                            </div>
                        </div>

                    </div>


                    <div class="form-group space-trigger-info space-dwell">
                        <label data-i18n="modal_1_when_trigger_dwell_every">Dwell Every: </label>
                        <input name="dwell" id="dwell" value="" class="form-control "/>
                        <label for="dwell" data-i18n="modal_1_when_trigger_dwell_label">Minutes ( min 30 minutes )</label>
                    </div>

                    <div class="form-group space-trigger-info space-every">
                        <label data-i18n="modal_1_when_trigger_dwell_recurring">Every: </label>
                        <input name="recurring" id="recurring" value="" class="form-control "/>
                        <label for="recurring" data-i18n="modal_1_when_trigger_recurring_label">Visit</label>
                    </div>


                    <div class="form-group space-trigger-info space-last">
                        <label data-i18n="modal_1_when_trigger_lastvisit_more_than">More than: </label>
                        <input name="lastvisit" id="lastvisit" value="" class="form-control "/>
                        <label for="lastvisit" data-i18n="modal_1_when_trigger_lastvisit_label">Days ( min 15 days )</label>
                    </div>


                    <div class="alert alert-primary mb-2" role="alert" data-i18n="modal_1_action">
                        Action
                    </div>


                    <div class="col-md-12 mb-1">

                        <div class="row mb-1">
                            <div class="col-12 pl-0">
                                <label class="label" data-i18n="modal_1_action_perform">Perform</label>
                            </div>
                        </div>

                        <div class="col-3 d-inline-flex pl-0">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="c_action" value="ads">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_action_perform_ads">Display Ads</label>
                            </div>
                        </div>

                        <div class="col-3 d-inline-flex pl-0">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="c_action" value="notification">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_action_perform_notification">Send Notification</label>
                            </div>
                        </div>


                        <div class="col-3 d-inline-flex">
                            <div class="vs-radio-con">
                                <input type="radio" class="uniform" name="c_action" id="action" value="redirect">
                                <span class="vs-radio">
                                    <span class="vs-radio--border"></span>
                                    <span class="vs-radio--circle"></span>
                                </span>
                                <label class="label" data-i18n="modal_1_action_perform_url">Redirect to URL</label>
                            </div>
                        </div>

                    </div>


                    <div class="form-group space-ads">

                        <label data-i18n="modal_1_action_ads_lib">Ads Library: </label>
                        <select name="ads_id" class="select2 form-control">
                            <option value="">None</option>
                            <?php

                            $kiw_temp = "SELECT name FROM kiwire_campaign_ads WHERE tenant_id = '{$tenant_id}'";
                            $kiw_rows = $kiw_db->fetch_array($kiw_temp);

                            foreach ($kiw_rows as $record) {

                                echo "<option value ='{$record['name']}'> {$record['name']}</option> \n";

                            }
                            ?>
                        </select>

                    </div>

                    <div class="form-group space-ads">

                        <label data-i18n="modal_1_action_ads_lib_space">Campaign Space: </label>
                        <select name="c_space" id="c_space" class="select2 form-control">
                            <option value="campaign-1" data-i18n="modal_1_action_ads_lib_space_opt1">Campaign 1</option>
                            <option value="campaign-2" data-i18n="modal_1_action_ads_lib_space_opt2">Campaign 2</option>
                            <option value="campaign-3" data-i18n="modal_1_action_ads_lib_space_opt3">Campaign 3</option>
                        </select>
                        <label class="label" for="c_space" data-i18n="modal_1_action_ads_lib_space_label">For campaign page with more than one space</label>

                    </div>

                    <div class="form-group space-notification">
                        <label data-i18n="modal_1_action_notification_method">Notification Method: </label>
                        <select name="notification_type" class="select2 form-control">
                            <option value="email" data-i18n="modal_1_action_notification_email">Email</option>
                            <option value="sms" data-i18n="modal_1_action_notification_sms">SMS</option>
                            <option value="api" data-i18n="modal_1_action_notification_api">API</option>
                            <option value="push" data-i18n="modal_1_action_notification_push">Web Push Notification</option>
                        </select>
                    </div>

                    <div class="form-group space-notification">

                        <div class="space-nontemplate">

                            <label for="notification_url" data-i18n="modal_1_action_url_template">Template / URL: </label>
                            <input type="text" name="notification_url" id="notification_url" class="form-control"/>
                            <label class="label" for="url" data-i18n="modal_1_action_url_label">String must be URL encoded</label>

                        </div>

                        <?php

                        $kiw_templates = $kiw_db->fetch_array("SELECT * FROM kiwire_html_template WHERE type IN ('email', 'sms') AND tenant_id = '{$_SESSION['tenant_id']}'");

                        ?>

                        <div class="space-template">

                            <label for="notification_url" data-i18n="modal_1_action_url_template_name">Template Name: </label>
                            <select name="notification_template" class="select2 form-control">

                                <option value="none" data-i18n="modal_1_action_url_template_name_optnone">None</option>

                                <?php foreach ($kiw_templates as $kiw_template){ ?>

                                    <option value='<?= $kiw_template['name'] ?>'><?= "[ " . strtoupper($kiw_template['type']) . " ] {$kiw_template['name']}" ?></option> <?= "\n" ?>

                                <?php } ?>

                            </select>
                        </div>

                    </div>

                    <div class="form-group space-redirection">
                        <label data-i18n="modal_1_action_url_url">URL: </label>
                        <input type="text" name="redirection" value="https://" class="form-control"/>
                        <label class="label" for="url" data-i18n="modal_1_action_url_url_label">URL for redirect user to. eg http or https://www.domain.com/</label>
                    </div>

                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                    
                </form>

            </div>


            <div class="modal-footer">

                <button type="button" class="btn btn-danger round waves-effect waves-light" data-dismiss="modal" data-i18n="modal_1_footer_button_cancel">Cancel</button>
                <button type="button" class="btn btn-primary round waves-effect waves-light btn-save" data-i18n="modal_1_footer_button_create">Create</button>

            </div>

        </div>
    </div>
</div>

<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>

<link rel="stylesheet" href="/assets/css/bootstrap-datepicker.css">
<script src="/assets/js/bootstrap-datepicker.min.js"></script>
